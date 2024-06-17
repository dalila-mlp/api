<?php

namespace App\Controller;

use App\Entity\ModelEntity;
use App\Entity\TransactionEntity;
use App\Enum\ModelName;
use App\Enum\ModelType;
use App\Enum\TransactionAction;
use App\Repository\ModelEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route("/model")]
final class ModelController extends AbstractController
{
    private string $githubModelsRepo;
    private string $githubToken;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ModelEntityRepository $modelRepository,
        private readonly SerializerInterface $serializer,
        private readonly HttpClientInterface $httpClient,
        string $githubModelsRepo,
        string $githubToken,
    ) {
        $this->githubModelsRepo = $githubModelsRepo;
        $this->githubToken = $githubToken;
    }

    #[Route("/names", methods: ["GET"])]
    public function getModelNames(): JsonResponse
    {
        return $this->json(array_map(fn($name) => $name->value, ModelName::cases()), Response::HTTP_OK);
    }

    #[Route("/types", methods: ["GET"])]
    public function getModelTypes(): JsonResponse
    {
        return $this->json(array_map(fn($type) => $type->value, ModelType::cases()), Response::HTTP_OK);
    }

    #[Route("s", methods: ["GET"])]
    public function getModels(): JsonResponse
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
    public function createModel(Request $request): JsonResponse
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
    public function getModel(string $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            $model,
            Response::HTTP_OK,
            [],
            ['groups' => ['model']],
        );
    }

    #[Route("/{id}/update", methods: ["PUT"])]
    public function updateModel(string $id, Request $request): JsonResponse
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
    public function getModelTransactions(string $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            array_map(
                function ($transaction) {
                    return [
                        'id' => $transaction->getId()->toString(),
                        'action' => $transaction->getAction()->value,
                        'active' => $transaction->getActive(),
                    ];
                },
                $model->getTransactions()->toArray(),
            ),
            Response::HTTP_OK,);
    }

    #[Route("/{id}/{transaction}/metrics", methods: ["GET"])]
    public function getModelMetrics(string $id, string $transaction): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            [
                "executionTime" => "1.38 hours",
                "accuracy" => "89.56 %",
                "precision" => "91.47 %",
                "recall" => "87.12 %",
                "f1Score" => "88.02 %"
            ],
            Response::HTTP_OK,
        );
    }
}
