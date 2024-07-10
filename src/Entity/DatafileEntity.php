<?php

namespace App\Entity;

use App\Repository\DatafileEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DatafileEntityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DatafileEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(["datafile"])]
    private UuidInterface $id;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["datafile"])]
    protected ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["datafile"])]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: "string", nullable: true)]
    #[Groups(["datafile"])]
    private string $status = "active";

    #[ORM\Column(type: "string")]
    #[Groups(["datafile"])]
    private ?string $weightUnitSize = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Groups(["datafile"])]
    private ?string $sha = null;

    public function __construct(
        #[ORM\Column(type: "string")]
        #[Groups(["datafile"])]
        private string $filename,
        #[ORM\Column(type: "float")]
        #[Groups(["datafile"])]
        private float $weight,
    ) {
        $this->setFilename($filename);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = str_replace(['-', '_'], ' ', str_replace(['  ', '.csv'], '', $filename));
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

    public function getSha(): ?string
    {
        return $this->sha;
    }

    public function setSha(?string $sha): self
    {
        $this->sha = $sha;
        return $this;
    }
}
