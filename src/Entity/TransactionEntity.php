<?php

namespace App\Entity;

use App\Enum\TransactionAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TransactionEntityRepository::class)]
class TransactionEntity
{
    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["transaction"])]
    protected ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["transaction"])]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[Pure]
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: "uuid", unique: true)]
        #[Groups(['transaction'])]
        private UuidInterface $id,
        #[ORM\Column(type: "string", enumType: TransactionAction::class)]
        #[Groups(['transaction'])]
        private TransactionAction $action, #[ORM\Column(type: "string", nullable: true)]
        #[Groups(['transaction'])]
        private bool $active = False, #[ORM\ManyToOne(inversedBy: 'transactions')]
        #[ORM\JoinColumn(nullable: false)]
        #[Groups(['transaction'])]
        private ?ModelEntity $model = null,
        #[ORM\OneToMany(targetEntity: MetricEntity::class, mappedBy: 'transaction', orphanRemoval: true)]
        #[Groups(['transaction'])]
        private Collection $metrics = new ArrayCollection,
        #[ORM\OneToMany(targetEntity: PlotEntity::class, mappedBy: 'transaction', orphanRemoval: true)]
        #[Groups(['transaction'])]
        private Collection $plots = new ArrayCollection,
        #[ORM\Column]
        private ?bool $deployed = False,
    ) {
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): static
    {
        $this->id = $id;

        return $this;
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

    /**
     * @return Collection<int, MetricEntity>
     */
    public function getMetrics(): Collection
    {
        return $this->metrics;
    }

    public function addMetric(MetricEntity $metric): static
    {
        if (!$this->metrics->contains($metric)) {
            $this->metrics->add($metric);
            $metric->setTransaction($this);
        }

        return $this;
    }

    public function removeMetric(MetricEntity $metric): static
    {
        if ($this->metrics->removeElement($metric)) {
            // set the owning side to null (unless already changed)
            if ($metric->getTransaction() === $this) {
                $metric->setTransaction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PlotEntity>
     */
    public function getPlots(): Collection
    {
        return $this->plots;
    }

    public function addPlot(PlotEntity $plot): static
    {
        if (!$this->plots->contains($plot)) {
            $this->plots->add($plot);
            $plot->setTransaction($this);
        }

        return $this;
    }

    public function removePlot(PlotEntity $plot): static
    {
        if ($this->plots->removeElement($plot)) {
            // set the owning side to null (unless already changed)
            if ($plot->getTransaction() === $this) {
                $plot->setTransaction(null);
            }
        }

        return $this;
    }

    public function isDeployed(): ?bool
    {
        return $this->deployed;
    }

    public function setDeployed(bool $deployed): static
    {
        $this->deployed = $deployed;

        return $this;
    }
}
