<?php

namespace App\Entity;

use App\Repository\IluoChecklistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IluoChecklistRepository::class)]
class IluoChecklist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'iluoChecklists')]
    private ?IluoLevels $iluoLevel = null;

    #[ORM\ManyToOne(inversedBy: 'iluoChecklists')]
    private ?Trainer $trainer = null;

    #[ORM\ManyToOne(inversedBy: 'iluoChecklists')]
    private ?ShiftLeaders $shiftLeader = null;

    #[ORM\ManyToOne(inversedBy: 'iluoChecklists')]
    private ?QualityRep $qualityRep = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validationDate = null;

    /**
     * @var Collection<int, Steps>
     */
    #[ORM\ManyToMany(targetEntity: Steps::class, inversedBy: 'iluoChecklists')]
    private Collection $step;

    #[ORM\OneToOne(inversedBy: 'iluoChecklist', cascade: ['persist', 'remove'])]
    private ?Iluo $iluo = null;

    public function __construct()
    {
        $this->step = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIluoLevel(): ?IluoLevels
    {
        return $this->iluoLevel;
    }

    public function setIluoLevel(?IluoLevels $iluoLevel): static
    {
        $this->iluoLevel = $iluoLevel;

        return $this;
    }

    public function getTrainer(): ?Trainer
    {
        return $this->trainer;
    }

    public function setTrainer(?Trainer $trainer): static
    {
        $this->trainer = $trainer;

        return $this;
    }

    public function getShiftLeader(): ?ShiftLeaders
    {
        return $this->shiftLeader;
    }

    public function setShiftLeader(?ShiftLeaders $shiftLeader): static
    {
        $this->shiftLeader = $shiftLeader;

        return $this;
    }

    public function getQualityRep(): ?QualityRep
    {
        return $this->qualityRep;
    }

    public function setQualityRep(?QualityRep $qualityRep): static
    {
        $this->qualityRep = $qualityRep;

        return $this;
    }

    public function getValidationDate(): ?\DateTimeInterface
    {
        return $this->validationDate;
    }

    public function setValidationDate(?\DateTimeInterface $validationDate): static
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    /**
     * @return Collection<int, Steps>
     */
    public function getStep(): Collection
    {
        return $this->step;
    }

    public function addStep(Steps $step): static
    {
        if (!$this->step->contains($step)) {
            $this->step->add($step);
        }

        return $this;
    }

    public function removeStep(Steps $step): static
    {
        $this->step->removeElement($step);

        return $this;
    }

    public function getIluo(): ?Iluo
    {
        return $this->iluo;
    }

    public function setIluo(?Iluo $iluo): static
    {
        $this->iluo = $iluo;

        return $this;
    }

}
