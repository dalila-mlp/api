<?php

namespace App\Controller;

use App\Entity\ModelEntity;
use App\Repository\ModelEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/model")]
final class ModelController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ModelEntityRepository $modelRepository,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route("s", methods: ["GET"])]
    public function getModels(): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($this->modelRepository->findAll(), 'json'),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true,
            JSON_UNESCAPED_UNICODE,
        );
    }

    #[Route("/create", methods: ["POST"])]
    public function createModel(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $model = new ModelEntity(
            id: 0, // This will be auto-generated
            filename: $data['filename'],
            name: $data['name'],
            type: $data['type'],
            status: $data['status'],
            uploadedAt: new \DateTime($data['uploadedAt']),
            uploadedBy: $data['uploadedBy'],
            weight: $data['weight'],
            flops: $data['flops'],
            lastTrain: new \DateTime($data['lastTrain']),
            deployed: $data['deployed']
        );

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return $this->json($model, 201);
    }

    #[Route("/{id}", methods: ["GET"])]
    public function getModel(int $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], 404);
        }

        return $this->json($model);
    }

    #[Route("/{id}/update", methods: ["PUT"])]
    public function updateModel(int $id, Request $request): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $model->setFilename($data['filename']);
        $model->setName($data['name']);
        $model->setType($data['type']);
        $model->setStatus($data['status']);
        $model->setUploadedAt(new \DateTime($data['uploadedAt']));
        $model->setUploadedBy($data['uploadedBy']);
        $model->setWeight($data['weight']);
        $model->setFlops($data['flops']);
        $model->setLastTrain(new \DateTime($data['lastTrain']));
        $model->setDeployed($data['deployed']);

        $this->entityManager->flush();

        return $this->json($model);
    }

    #[Route("/{id}/delete", methods: ["DELETE"])]
    public function deleteModel(int $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], 404);
        }

        $this->entityManager->remove($model);
        $this->entityManager->flush();

        return $this->json(['message' => 'Model deleted successfully']);
    }
}
