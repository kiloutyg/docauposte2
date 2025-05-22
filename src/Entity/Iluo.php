<?php

namespace App\Entity;

use App\Repository\IluoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IluoRepository::class)]
class Iluo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'iluos')]
    private ?Operator $operator;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\ManyToOne(inversedBy: 'iluos')]
    private ?Products $product = null;

    #[ORM\ManyToOne(inversedBy: 'iluos')]
    private ?Workstation $workstation = null;

    #[ORM\ManyToOne(inversedBy: 'iluos')]
    private ?Trainer $trainer = null;

    #[ORM\ManyToOne(inversedBy: 'iluos')]
    private ?ShiftLeaders $shiftLeader = null;

    #[ORM\ManyToOne(inversedBy: 'iluos')]
    private ?QualityRep $qualityRep = null;

    #[ORM\OneToOne(mappedBy: 'iluo', cascade: ['persist', 'remove'])]
    private ?IluoChecklist $iluoChecklist = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOperator(): Operator
    {
        return $this->operator;
    }

    public function setOperator(?Operator $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getWorkstation(): ?Workstation
    {
        return $this->workstation;
    }

    public function setWorkstation(?Workstation $workstation): static
    {
        $this->workstation = $workstation;

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

    public function getIluoChecklist(): ?IluoChecklist
    {
        return $this->iluoChecklist;
    }

    public function setIluoChecklist(?IluoChecklist $iluoChecklist): static
    {
        // unset the owning side of the relation if necessary
        if ($iluoChecklist === null && $this->iluoChecklist !== null) {
            $this->iluoChecklist->setIluo(null);
        }

        // set the owning side of the relation if necessary
        if ($iluoChecklist !== null && $iluoChecklist->getIluo() !== $this) {
            $iluoChecklist->setIluo($this);
        }

        $this->iluoChecklist = $iluoChecklist;

        return $this;
    }

}
