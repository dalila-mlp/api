<?php

namespace App\Controller;

use App\Entity\DatafileEntity;
use App\Repository\DatafileEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/datafile")]
final class DatafileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DatafileEntityRepository $datafileRepository,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route("s", methods: ["GET"])]
    public function getDatafiles(): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($this->datafileRepository->findAll(), 'json'),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true,
            JSON_UNESCAPED_UNICODE,
        );
    }

    #[Route("/create", methods: ["POST"])]
    public function createDatafile(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $datafile = new DatafileEntity(
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

        $this->entityManager->persist($datafile);
        $this->entityManager->flush();

        return $this->json($datafile, 201);
    }

    #[Route("/{id}", methods: ["GET"])]
    public function getDatafile(int $id): JsonResponse
    {
        $datafile = $this->datafileRepository->find($id);

        if (!$datafile) {
            return $this->json(['message' => 'Datafile not found'], 404);
        }

        return $this->json($datafile);
    }

    #[Route("/{id}/update", methods: ["PUT"])]
    public function updateDatafile(int $id, Request $request): JsonResponse
    {
        $datafile = $this->datafileRepository->find($id);

        if (!$datafile) {
            return $this->json(['message' => 'Datafile not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $datafile->setFilename($data['filename']);
        $datafile->setName($data['name']);
        $datafile->setType($data['type']);
        $datafile->setStatus($data['status']);
        $datafile->setUploadedAt(new \DateTime($data['uploadedAt']));
        $datafile->setUploadedBy($data['uploadedBy']);
        $datafile->setWeight($data['weight']);

        $this->entityManager->flush();

        return $this->json($datafile);
    }

    #[Route("/{id}/delete", methods: ["DELETE"])]
    public function deleteDatafile(int $id): JsonResponse
    {
        $datafile = $this->datafileRepository->find($id);

        if (!$datafile) {
            return $this->json(['message' => 'Datafile not found'], 404);
        }

        $this->entityManager->remove($datafile);
        $this->entityManager->flush();

        return $this->json(['message' => 'Datafile deleted successfully']);
    }
}
