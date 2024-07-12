<?php

namespace App\Controller;

use App\Entity\ModelEntity;
use App\Entity\MetricEntity;
use App\Entity\PlotEntity;
use App\Entity\TransactionEntity;
use App\Enum\ModelName;
use App\Enum\ModelType;
use App\Enum\TransactionAction;
use App\Repository\DatafileEntityRepository;
use App\Repository\ModelEntityRepository;
use App\Repository\TransactionEntityRepository;
use App\Service\ModelParameters;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Writer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

#[Route("/model")]
final class ModelController extends AbstractController
{
    private string $githubModelsRepo;
    private string $githubPlotsRepo;
    private string $githubToken;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DatafileEntityRepository $datafileRepository,
        private readonly ModelEntityRepository $modelRepository,
        private readonly TransactionEntityRepository $transactionRepository,
        private readonly SerializerInterface $serializer,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        string $githubModelsRepo,
        string $githubPlotsRepo,
        string $githubToken,
    ) {
        $this->githubModelsRepo = $githubModelsRepo;
        $this->githubPlotsRepo = $githubPlotsRepo;
        $this->githubToken = $githubToken;
    }

    #[Route("/names", methods: ["GET"])]
    public function getNames(): JsonResponse
    {
        return $this->json(array_map(fn($name) => $name->value, ModelName::cases()), Response::HTTP_OK);
    }

    #[Route("/types", methods: ["GET"])]
    public function getTypes(): JsonResponse
    {
        return $this->json(array_map(fn($type) => $type->value, ModelType::cases()), Response::HTTP_OK);
    }

    #[Route("s", methods: ["GET"])]
    public function gets(): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($this->modelRepository->findAll(), 'json', ['groups' => ['model']]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true,
            JSON_UNESCAPED_UNICODE,
        );
    }

    #[Route("/create", methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['message' => 'No file uploaded!'], Response::HTTP_BAD_REQUEST);
        }

        if ($file->getClientOriginalExtension() !== 'py') {
            return $this->json(['message' => 'Invalid file extension! Only .py files are allowed.'], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->request->all();

        if (empty($data['name']) || empty($data['type'])) {
            return $this->json(['message' => 'Missing required fields!'], Response::HTTP_BAD_REQUEST);
        }

        if (!ModelName::tryFrom($data['name']) || !ModelType::tryFrom($data['type'])) {
            return $this->json(['message' => 'Invalid name or type value!'], Response::HTTP_BAD_REQUEST);
        }

        $model = new ModelEntity(
            filename: $file->getClientOriginalName(),
            name: ModelName::from($data['name']),
            type: ModelType::from($data['type']),
            weight: $file->getSize(),
        );

        $transaction = new TransactionEntity(
            id: Uuid::uuid4(),
            action: TransactionAction::CREATION,
            active: true,
            model: $model,
        );

        $this->entityManager->persist($model);
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/models/' . $model->getId() . '.py';
        $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/models', $model->getId() . '.py');
        $fileContent = base64_encode(file_get_contents($filePath));

        $response = $this->httpClient->request(
            'PUT',
            $this->githubModelsRepo . "contents/" . $model->getId() . '.py',
            [
                'headers' => [
                    'Authorization' => 'token ' . $this->githubToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => "upload({$model->getId()}): {$model->getFilename()}",
                    'content' => $fileContent,
                ],
            ]
        );

        unlink($filePath); // Delete local file after successful upload.
        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            $this->entityManager->remove($model);
            $this->entityManager->flush();

            return $this->json(['message' => 'Failed to upload model to GitHub!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $model->setSha(json_decode($response->getContent(), true)['content']['sha']); 
        $this->entityManager->flush();

        return $this->json($model, Response::HTTP_CREATED);
    }

    #[Route("/{id}", methods: ["GET"])]
    public function get(string $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            $model,
            Response::HTTP_OK,
            [],
            ['groups' => ['model', 'model.owner']],
        );
    }

    #[Route("/{id}/update", methods: ["PUT"])]
    public function update(string $id, Request $request): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!ModelName::tryFrom($data['name']) || !ModelType::tryFrom($data['type'])) {
            return $this->json(['message' => 'Invalid name or type value.'], Response::HTTP_BAD_REQUEST);
        }

        $model->setFilename($data['filename']);
        $model->setName($data['name']);
        $model->setType($data['type']);
        $model->setStatus($data['status']);
        $model->setWeight($data['weight']);

        $this->entityManager->flush();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    #[Route("/{id}/delete", methods: ["DELETE"])]
    public function deleteModel(string $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $response = $this->httpClient->request(
            'DELETE',
            $this->githubModelsRepo . 'contents/' . $model->getId() . '.py',
            [
                'headers' => [
                    'Authorization' => 'token ' . $this->githubToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => "remove({$model->getId()}): {$model->getFilename()}",
                    'sha' => $model->getSha(),
                ],
            ]
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return $this->json(['message' => 'Failed to delete model from GitHub!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->entityManager->remove($model);
        $this->entityManager->flush();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    #[Route("/{id}/transactions", methods: ["GET"])]
    public function transactions(string $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);
        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $transactions = $model->getTransactions()->toArray();
        usort(
            $transactions,
            fn ($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt(),
        );

        return $this->json(
            array_map(
                function ($transaction) {
                    return [
                        'id' => $transaction->getId()->toString(),
                        'action' => $transaction->getAction()->value,
                        'active' => $transaction->getActive(),
                        'deployed' => $transaction->isDeployed(),
                    ];
                },
                $transactions,
            ),
            Response::HTTP_OK,
        );
    }

    #[Route("/{transactionId}/metrics", methods: ["GET"])]
    public function metrics(string $transactionId): JsonResponse
    {
        $transaction = $this->transactionRepository->find($transactionId);
        if (!$transaction) {
            return $this->json(['message' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            array_reduce(
                $transaction->getMetrics()->toArray(),
                function($carry, $metric) {
                    $name = $metric->getName();
                    $value = $metric->getValue();
    
                    if ($name === 'executionTime') {
                        $carry[$name] = sprintf('%.2f hours', $value);
                    } else {
                        $carry[$name] = sprintf('%.2f %%', $value);
                    }
    
                    return $carry;
                },
                [],
            ),
            Response::HTTP_OK,
        );
    }

    #[Route("/{transactionId}/plots", methods: ["GET"])]
    public function plots(string $transactionId): JsonResponse
    {
        $transaction = $this->transactionRepository->find($transactionId);
        if (!$transaction) {
            return $this->json(['message' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }

        $plots = $transaction->getPlots()->toArray();

        $imageUrls = [];
        foreach ($plots as $plot) {
            $response = $this->httpClient->request(
                'GET',
                $this->githubPlotsRepo . "contents/" . $plot->getId() . ".png",
                [
                    'headers' => [
                        'Authorization' => 'token ' . $this->githubToken,
                        'Content-Type' => 'application/json',
                    ],
                ],
            );

            if ($response->getStatusCode() === 200) {
                $imageData = $response->toArray();
                $imageUrls[] = $imageData['download_url'];
            } else {
                // Gérer l'erreur pour ce plot spécifique, par exemple en ajoutant un message d'erreur dans la liste
                $imageUrls[] = ['error' => 'Image not found for plot ID ' . $plot->getId()];
            }
        }

        return $this->json($imageUrls, Response::HTTP_OK);
    }

    #[Route("/{id}/parameters", methods: ["GET"])]
    public function parameters(string $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);
        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            ModelParameters::get($model->getType(), $model->getName()),
            Response::HTTP_OK,
        );
    }

    #[Route("/info", methods: ["POST"])]
    public function info(Request $request): JsonResponse
    {
        $response = $this->httpClient->request(
            "POST",
            "http://dalila-llm_api-service:18950/model_info",
            [
                "headers" => ["Content-Type" => "application/json"],
                "json" => [
                    "content" => file_get_contents($request->files->get('file')->getPathname()),
                    "model_names" => array_map(fn($type) => $type->value, ModelName::cases()),
                    "model_types" => array_map(fn($name) => $name->value, ModelType::cases()),
                ],
            ],
        );

        return $this->json(json_decode($response->getContent(), true), Response::HTTP_OK);
    }

    #[Route("/train", methods: ["POST"])]
    public function train(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $model = $this->modelRepository->find($data['model_id']);
        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $datafile = $this->datafileRepository->find($data['datafile_id']);
        if (!$datafile) {
            return $this->json(['message' => 'Datafile not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$data['parameters']) {
            return $this->json(['message' => 'Parameters not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = $this->httpClient->request(
            'POST',
            'http://dalila-models_api-service:14506/train',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'model_id' => $data['model_id'],
                    'datafile_id' => $data['datafile_id'],
                    'target_column' => $data['target_column'],
                    'features' => $data['features'],
                    'test_size' => $data['test_size'],
                    'model_type' => $model->getType()->value,
                    'parameters' => $data['parameters'],
                ],
            ],
        );
        $responseData = json_decode($response->getContent(), true);

        if (
            !$responseData['metrics'] ||
            !$responseData['plot_id_list'] ||
            !$responseData['model_save_id'] ||
            !$responseData['model_type']
        ) {
            return $this->json(['message' => 'Training went badly...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $model
            ->setLastTrain(new \DateTime('now'))
            ->setLibType($responseData['model_type'])
        ;

        $transaction = new TransactionEntity(
            id: Uuid::fromString($responseData['model_save_id']),
            action: TransactionAction::TRAIN,
            active: true,
            model: $model,
        );

        $filteredMetricsData = array_filter(
            $responseData['metrics'],
            fn($value, $name) => $name !== 'confusion_matrix',
            ARRAY_FILTER_USE_BOTH,
        );

        $metrics = (new ArrayCollection(
            array_map(
                fn($name, $value) => (
                    (new MetricEntity())
                        ->setName($name)
                        ->setValue($value)
                        ->setModel($model)
                        ->setTransaction($transaction)
                ),
                array_keys($filteredMetricsData),
                $filteredMetricsData,
            ),
        ))->toArray();

        $plots = (new ArrayCollection(
            array_map(
                fn($id) => (
                    (new PlotEntity())
                        ->setId(Uuid::fromString($id))
                        ->setModel($model)
                        ->setTransaction($transaction)
                ),
                $responseData['plot_id_list'],
            )
        ))->toArray();

        // Update all previous transactions to inactive using QueryBuilder
        $this->entityManager->createQueryBuilder()
            ->update(TransactionEntity::class, 't')
            ->set('t.active', ':inactive')
            ->where('t.model = :model')
            ->setParameter('inactive', '0')
            ->setParameter('model', $model)
            ->getQuery()
            ->execute()
        ;

        array_walk($metrics, fn($metric) => $this->entityManager->persist($metric));
        array_walk($plots, fn($plot) => $this->entityManager->persist($plot));
        $this->entityManager->persist($model);
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $this->json(['model_id' => $model->getId(), 'debug' => $model->getType()->value], Response::HTTP_OK);
    }

    #[Route("/predict", methods: ["POST"])]
    public function predict(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $model = $this->modelRepository->find($data["model_id"]);
        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $datafile = $this->datafileRepository->find($data["datafile_id"]);
        if (!$datafile) {
            return $this->json(['message' => 'Datafile not found'], Response::HTTP_NOT_FOUND);
        }

        $deployedTransaction = array_filter($model->getTransactions()->toArray(), function($transaction) {
            return $transaction->isDeployed();
        });

        if (empty($deployedTransaction)) {
            return $this->json(['message' => 'No deployed transaction found for this model'], Response::HTTP_BAD_REQUEST);
        }

        // Assuming only one deployed transaction exists, get the first one
        $deployedTransaction = reset($deployedTransaction);

        $response = $this->httpClient->request(
            "POST",
            "http://dalila-models_api-service:14506/predict",
            [
                "headers" => ["Content-Type" => "application/json"],
                "json" => [
                    "model_id" => $deployedTransaction->getId()->toString(),
                    "datafile_id" => $data["datafile_id"],
                    "features" => $data["features"],
                    "model_type" => $model->getLibType(),
                ],
            ],
        );
        $responseData = json_decode($response->getContent(), true);

        if (empty($responseData)) {
            return $this->json(['message' => 'No prediction data returned'], Response::HTTP_BAD_REQUEST);
        }

        // Convert JSON data to CSV
        $csv = Writer::createFromString('');
        // Insert headers based on the keys of the first element of the response data
        $headers = array_keys(reset($responseData));
        $csv->insertOne($headers);

        foreach ($responseData as $row) {
            $csv->insertOne($row);
        }

        // Create a Response object and set the content
        $csvContent = $csv->toString();
        $response = new Response($csvContent);
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'predictions.csv');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }

    #[Route("/deploy", methods: ["POST"])]
    public function deploy(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $model = $this->modelRepository->find($data['model_id']);
        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $transaction = $this->transactionRepository->find($data['transaction_id']);
        if (!$transaction || !$model->hasTransaction(Uuid::fromString($data['transaction_id']))) {
            return $this->json(['message' => 'Transaction not exist!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Set deployed to false for all transactions of the model
        foreach ($model->getTransactions() as $modelTransaction) {
            $modelTransaction->setDeployed(false);
        }

        $model->setDeployed(True);
        $transaction->setDeployed(True);

        $this->entityManager->persist($model);
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $this->json(['message' => 'Transaction deployed successfully!', Response::HTTP_OK]);
    }
}
