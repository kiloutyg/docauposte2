<?php

namespace App\Entity;

use App\Repository\TrainerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrainerRepository::class)]
class Trainer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'trainer', cascade: ['persist', 'remove'])]
    private ?Operator $operator = null;

    #[ORM\OneToMany(mappedBy: 'trainer', targetEntity: TrainingRecord::class)]
    private Collection $trainingRecords;

    public function __construct()
    {
        $this->trainingRecords = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOperator(): ?Operator
    {
        return $this->operator;
    }

    public function setOperator(?Operator $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return Collection<int, TrainingRecord>
     */
    public function getTrainingRecords(): Collection
    {
        return $this->trainingRecords;
    }

    public function addTrainingRecord(TrainingRecord $trainingRecord): static
    {
        if (!$this->trainingRecords->contains($trainingRecord)) {
            $this->trainingRecords->add($trainingRecord);
            $trainingRecord->setTrainer($this);
        }

        return $this;
    }

    public function removeTrainingRecord(TrainingRecord $trainingRecord): static
    {
        if ($this->trainingRecords->removeElement($trainingRecord)) {
            // set the owning side to null (unless already changed)
            if ($trainingRecord->getTrainer() === $this) {
                $trainingRecord->setTrainer(null);
            }
        }

        return $this;
    }
}
