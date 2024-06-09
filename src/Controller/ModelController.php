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
use Ramsey\Uuid\UuidInterface;

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
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['message' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        if ($file->getClientOriginalExtension() !== 'py') {
            return $this->json(['message' => 'Invalid file extension. Only .py files are allowed.'], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->request->all();

        if (empty($data['name']) || empty($data['type'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $model = new ModelEntity(
            filename: $file->getClientOriginalName(),
            name: $data['name'],
            type: $data['type'],
            weight: $file->getSize(),
        );

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        try {
            $file->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/models',
                $model->getId() . '.py',
            );
        } catch (FileException $e) {
            return $this->json(['message' => 'Failed to upload file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($model, Response::HTTP_CREATED);
    }

    #[Route("/{id}", methods: ["GET"])]
    public function getModel(int $id): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($model, Response::HTTP_OK);
    }

    #[Route("/{id}/update", methods: ["PUT"])]
    public function updateModel(int $id, Request $request): JsonResponse
    {
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
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

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    #[Route("/{id}/delete", methods: ["DELETE"])]
    public function deleteModel(string $id): JsonResponse
    {
        error_log($id);
        $model = $this->modelRepository->find($id);

        if (!$model) {
            return $this->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/models/' . $model->getId() . '.py';

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->entityManager->remove($model);
        $this->entityManager->flush();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
