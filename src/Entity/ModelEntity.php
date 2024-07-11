<?php

namespace App\Entity;

use App\Enum\ModelName;
use App\Enum\ModelType;
use App\Repository\ModelEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ModelEntityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ModelEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['model', 'transaction'])]
    private UuidInterface $id;
    
    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["model"])]
    protected ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["model"])]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: "string", nullable: true)]
    #[Groups(['model'])]
    private string $status = "active";

    #[ORM\Column(type: "string")]
    #[Groups(['model'])]
    private ?string $weightUnitSize = null;

    #[ORM\Column(type: "float", nullable: true)]
    #[Groups(['model'])]
    private float $flops = 0.0;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Groups(['model'])]
    private ?\DateTimeInterface $lastTrain = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    #[Groups(['model'])]
    private bool $deployed = false;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Groups(['model'])]
    private ?string $sha = null;

    #[Gedmo\Blameable(on: "create")]
    #[ORM\ManyToOne(inversedBy: 'models')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['model', 'model.owner'])]
    private ?UserEntity $owner;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libType = null;

    #[Pure] public function __construct(
        #[ORM\Column(type: "string")]
        #[Groups(['model'])]
        private string $filename,
        #[ORM\Column(type: "string", enumType: ModelName::class)]
        #[Groups(['model'])]
        private ModelName $name,
        #[ORM\Column(type: "string", enumType: ModelType::class)]
        #[Groups(['model'])]
        private ModelType $type,
        #[ORM\Column(type: "float")]
        #[Groups(['model'])]
        private float $weight,
        #[ORM\OneToMany(targetEntity: TransactionEntity::class, mappedBy: 'model', orphanRemoval: true)]
        #[Groups(['model'])]
        private Collection $transactions = new ArrayCollection,
        #[ORM\OneToMany(targetEntity: MetricEntity::class, mappedBy: 'model', orphanRemoval: true)]
        #[Groups(['model'])]
        private Collection $metrics = new ArrayCollection,
        #[ORM\OneToMany(targetEntity: PlotEntity::class, mappedBy: 'model', orphanRemoval: true)]
        #[Groups(['model'])]
        private Collection $plots = new ArrayCollection,
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

    public function setFilename(string $filename): static
    {
        $this->filename = str_replace(['-', '_'], ' ', str_replace(['  ', '.py'], '', $filename));

        return $this;
    }

    public function getName(): ModelName
    {
        return $this->name;
    }

    public function setName(ModelName $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ModelType
    {
        return $this->type;
    }

    public function setType(ModelType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeightUnitSize(): string
    {
        return $this->weightUnitSize;
    }

    #[ORM\PrePersist]
    public function setWeightUnitSize(): static
    {
        $this->weightUnitSize = ['B', 'KB', 'MB', 'GB', 'TB'][$this->getWeight() > 0 ? floor(log($this->getWeight(), 1024)) : 0];

        return $this;
    }

    public function getFlops(): float
    {
        return $this->flops;
    }

    public function setFlops(float $flops): static
    {
        $this->flops = $flops;

        return $this;
    }

    public function getLastTrain(): \DateTimeInterface
    {
        return $this->lastTrain;
    }

    public function setLastTrain(\DateTimeInterface $lastTrain): static
    {
        $this->lastTrain = $lastTrain;

        return $this;
    }

    public function isDeployed(): bool
    {
        return $this->deployed;
    }

    public function setDeployed(bool $deployed): static
    {
        $this->deployed = $deployed;

        return $this;
    }

    public function getSha(): ?string
    {
        return $this->sha;
    }

    public function setSha(?string $sha): static
    {
        $this->sha = $sha;

        return $this;
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(TransactionEntity $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setModel($this);
        }

        return $this;
    }

    public function removeTransaction(TransactionEntity $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            if ($transaction->getModel() === $this) {
                $transaction->setModel(null);
            }
        }

        return $this;
    }

    public function hasTransaction(UuidInterface $transactionId): bool
    {
        foreach ($this->transactions as $transaction) {
            if ($transaction->getId()->equals($transactionId)) {
                return true;
            }
        }

        return false;
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
            $metric->setModel($this);
        }

        return $this;
    }

    public function removeMetric(MetricEntity $metric): static
    {
        if ($this->metrics->removeElement($metric)) {
            // set the owning side to null (unless already changed)
            if ($metric->getModel() === $this) {
                $metric->setModel(null);
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
            $plot->setModel($this);
        }

        return $this;
    }

    public function removePlot(PlotEntity $plot): static
    {
        if ($this->plots->removeElement($plot)) {
            // set the owning side to null (unless already changed)
            if ($plot->getModel() === $this) {
                $plot->setModel(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?UserEntity
    {
        return $this->owner;
    }

    public function setOwner(?UserEntity $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getLibType(): ?string
    {
        return $this->libType;
    }

    public function setLibType(string $libType): static
    {
        $this->libType = $libType;

        return $this;
    }
}
