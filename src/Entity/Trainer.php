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

    #[ORM\OneToOne(inversedBy: 'trainer')]
    private ?Operator $operator = null;

    #[ORM\OneToMany(mappedBy: 'trainer', targetEntity: TrainingRecord::class)]
    private Collection $trainingRecords;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $demoted;

    /**
     * @var Collection<int, Iluo>
     */
    #[ORM\OneToMany(targetEntity: Iluo::class, mappedBy: 'trainer')]
    private Collection $iluos;

    /**
     * @var Collection<int, IluoChecklist>
     */
    #[ORM\OneToMany(targetEntity: IluoChecklist::class, mappedBy: 'trainer')]
    private Collection $iluoChecklists;

    public function __construct()
    {
        $this->trainingRecords = new ArrayCollection();
        $this->iluos = new ArrayCollection();
        $this->iluoChecklists = new ArrayCollection();
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

    public function isDemoted(): ?bool
    {
        return $this->demoted;
    }

    public function setDemoted(?bool $demoted): static
    {
        $this->demoted = $demoted;

        return $this;
    }

    /**
     * @return Collection<int, Iluo>
     */
    public function getIluos(): Collection
    {
        return $this->iluos;
    }

    public function addIluo(Iluo $iluo): static
    {
        if (!$this->iluos->contains($iluo)) {
            $this->iluos->add($iluo);
            $iluo->setTrainer($this);
        }

        return $this;
    }

    public function removeIluo(Iluo $iluo): static
    {
        if ($this->iluos->removeElement($iluo)) {
            // set the owning side to null (unless already changed)
            if ($iluo->getTrainer() === $this) {
                $iluo->setTrainer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IluoChecklist>
     */
    public function getIluoChecklists(): Collection
    {
        return $this->iluoChecklists;
    }

    public function addIluoChecklist(IluoChecklist $iluoChecklist): static
    {
        if (!$this->iluoChecklists->contains($iluoChecklist)) {
            $this->iluoChecklists->add($iluoChecklist);
            $iluoChecklist->setTrainer($this);
        }

        return $this;
    }

    public function removeIluoChecklist(IluoChecklist $iluoChecklist): static
    {
        if ($this->iluoChecklists->removeElement($iluoChecklist)) {
            // set the owning side to null (unless already changed)
            if ($iluoChecklist->getTrainer() === $this) {
                $iluoChecklist->setTrainer(null);
            }
        }

        return $this;
    }
}
