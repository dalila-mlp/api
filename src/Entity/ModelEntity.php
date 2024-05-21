<?php

namespace App\Entity;

use App\Repository\ModelEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ModelEntityRepository::class)]
class ModelEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: "integer")]
        private int $id,
        #[ORM\Column(type: "string")]
        private string $filename,
        #[ORM\Column(type: "string")]
        private string $name,
        #[ORM\Column(type: "string")]
        private string $type,
        #[ORM\Column(type: "string")]
        private string $status,
        #[ORM\Column(type: "datetime")]
        private \DateTimeInterface $uploadedAt,
        #[ORM\Column(type: "string")]
        private string $uploadedBy,
        #[ORM\Column(type: "float")]
        private float $weight,
        #[ORM\Column(type: "string")]
        private string $weightUnitSize,
        #[ORM\Column(type: "float")]
        private float $flops,
        #[ORM\Column(type: "datetime")]
        private \DateTimeInterface $lastTrain,
        #[ORM\Column(type: "boolean")]
        private bool $deployed,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getUploadedAt(): \DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeInterface $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    public function getUploadedBy(): string
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(string $uploadedBy): void
    {
        $this->uploadedBy = $uploadedBy;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    public function getWeightUnitSize(): string
    {
        return $this->weightUnitSize;
    }

    public function setWeightUnitSize(string $weightUnitSize): void
    {
        $this->weightUnitSize = $weightUnitSize;
    }

    public function getFlops(): float
    {
        return $this->flops;
    }

    public function setFlops(float $flops): void
    {
        $this->flops = $flops;
    }

    public function getLastTrain(): \DateTimeInterface
    {
        return $this->lastTrain;
    }

    public function setLastTrain(\DateTimeInterface $lastTrain): void
    {
        $this->lastTrain = $lastTrain;
    }

    public function isDeployed(): bool
    {
        return $this->deployed;
    }

    public function setDeployed(bool $deployed): void
    {
        $this->deployed = $deployed;
    }
}
