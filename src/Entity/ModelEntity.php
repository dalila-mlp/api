<?php

namespace App\Entity;

use App\Repository\ModelEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: ModelEntityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ModelEntity
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\Column(type: "string", nullable: true)]
    private string $status = "inactive";

    #[ORM\Column(type: "string", nullable: true)]
    private string $uploadedBy = "incomming";

    #[ORM\Column(type: "string")]
    private ?string $weightUnitSize = null;

    #[ORM\Column(type: "float", nullable: true)]
    private float $flops = 0.0;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $lastTrain = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    private bool $deployed = false;

    #[Pure] public function __construct(
        #[ORM\Column(type: "string")]
        private string $filename,
        #[ORM\Column(type: "string")]
        private string $name,
        #[ORM\Column(type: "string")]
        private string $type,
        #[ORM\Column(type: "float")]
        private float $weight,
    ) {
        $this->setFilename($filename);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = str_replace(['-', '_'], ' ', str_replace(['  ', '.py'], '', $filename));
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

    #[ORM\PrePersist]
    public function setWeightUnitSize(): void
    {
        $this->weightUnitSize = ['B', 'KB', 'MB', 'GB', 'TB'][$this->getWeight() > 0 ? floor(log($this->getWeight(), 1024)) : 0];
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
