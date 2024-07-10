<?php

namespace App\Entity;

use App\Repository\MetricRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity(repositoryClass: MetricRepository::class)]
class MetricEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['metric'])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Groups(['metric'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['metric'])]
    private ?float $value = null;

    #[ORM\ManyToOne(inversedBy: 'metrics')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['metric'])]
    private ?ModelEntity $model = null;

    #[ORM\ManyToOne(inversedBy: 'metrics')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['metric'])]
    private ?TransactionEntity $transaction = null;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getModel(): ?ModelEntity
    {
        return $this->model;
    }

    public function setModel(?ModelEntity $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getTransaction(): ?TransactionEntity
    {
        return $this->transaction;
    }

    public function setTransaction(?TransactionEntity $transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }
}
