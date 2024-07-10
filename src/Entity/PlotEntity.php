<?php

namespace App\Entity;

use App\Repository\PlotEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: PlotEntityRepository::class)]
class PlotEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[Groups(['plot'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(inversedBy: 'plots')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['plot'])]
    private ?ModelEntity $model = null;

    #[ORM\ManyToOne(inversedBy: 'plots')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['plot'])]
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
