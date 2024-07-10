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
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route("/datafile")]
final class DatafileController extends AbstractController
{
    private string $githubDatafilesRepo;
    private string $githubToken;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DatafileEntityRepository $datafileRepository,
        private readonly SerializerInterface $serializer,
        private readonly HttpClientInterface $httpClient,
        string $githubDatafilesRepo,
        string $githubToken,
    ) {
        $this->githubDatafilesRepo = $githubDatafilesRepo;
        $this->githubToken = $githubToken;
    }

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
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['message' => 'No file uploaded!'], Response::HTTP_BAD_REQUEST);
        }

        if ($file->getClientOriginalExtension() !== 'csv') {
            return $this->json(['message' => 'Invalid file extension! Only .csv files are allowed.'], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->request->all();

        $datafile = new DatafileEntity(
            filename: $file->getClientOriginalName(),
            weight: $file->getSize(),
        );

        $this->entityManager->persist($datafile);
        $this->entityManager->flush();

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/datafiles/' . $datafile->getId() . '.csv';
        $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/datafiles', $datafile->getId() . '.csv');
        $fileContent = base64_encode(file_get_contents($filePath));

        $response = $this->httpClient->request(
            'PUT',
            $this->githubDatafilesRepo . "contents/" . $datafile->getId() . '.csv',
            [
                'headers' => [
                    'Authorization' => 'token ' . $this->githubToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => "upload({$datafile->getId()}): {$datafile->getFilename()}",
                    'content' => $fileContent,
                ],
            ]
        );

        unlink($filePath); // Delete local file after successful upload.
        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            $this->entityManager->remove($datafile);
            $this->entityManager->flush();

            return $this->json(['message' => 'Failed to upload datefile to GitHub!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $datafile->setSha(json_decode($response->getContent(), true)['content']['sha']);
        $this->entityManager->flush();

        return $this->json($datafile, Response::HTTP_CREATED);
    }

    #[Route("/{id}", methods: ["GET"])]
    public function getDatafile(string $id): JsonResponse
    {
        $datafile = $this->datafileRepository->find($id);

        if (!$datafile) {
            return $this->json(['message' => 'Datafile not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($datafile, Response::HTTP_OK);
    }

    #[Route("/{id}/update", methods: ["PUT"])]
    public function updateDatafile(string $id, Request $request): JsonResponse
    {
        $datafile = $this->datafileRepository->find($id);

        if (!$datafile) {
            return $this->json(['message' => 'Datafile not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $datafile->setFilename($data['filename']);
        $datafile->setWeight($data['weight']);

        $this->entityManager->flush();

        return $this->json($datafile, Response::HTTP_NO_CONTENT);
    }

    #[Route("/{id}/delete", methods: ["DELETE"])]
    public function deleteDatafile(string $id): JsonResponse
    {
        $datafile = $this->datafileRepository->find($id);

        if (!$datafile) {
            return $this->json(['message' => 'Datafile not found'], Response::HTTP_NOT_FOUND);
        }

        $response = $this->httpClient->request(
            'DELETE',
            $this->githubDatafilesRepo . 'contents/' . $datafile->getId() . '.csv',
            [
                'headers' => [
                    'Authorization' => 'token ' . $this->githubToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => "remove({$datafile->getId()}): {$datafile->getFilename()}",
                    'sha' => $datafile->getSha(),
                ],
            ]
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return $this->json(['message' => 'Failed to delete datafile from GitHub!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->entityManager->remove($datafile);
        $this->entityManager->flush();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
