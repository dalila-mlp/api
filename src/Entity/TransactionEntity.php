<?php

namespace App\Entity;

use App\Enum\TransactionAction;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TransactionEntityRepository::class)]
class TransactionEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['transaction'])]
    private UuidInterface $id;
    
    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["transaction"])]
    protected ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["transaction"])]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[Pure] public function __construct(
        #[ORM\Column(type: "string", enumType: TransactionAction::class)]
        #[Groups(['transaction'])]
        private TransactionAction $action,
        #[ORM\Column(type: "string", nullable: true)]
        #[Groups(['transaction'])]
        private bool $active = False,
        #[ORM\ManyToOne(inversedBy: 'transactions')]
        #[ORM\JoinColumn(nullable: false)]
        #[Groups(['transaction'])]
        private ?ModelEntity $model = null,
    ) {}

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

    public function getAction(): TransactionAction
    {
        return $this->action;
    }

    public function setAction(TransactionAction $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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
}
