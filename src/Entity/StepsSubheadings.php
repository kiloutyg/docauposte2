<?php

namespace App\Entity;

use App\Repository\StepsSubheadingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StepsSubheadingsRepository::class)]
class StepsSubheadings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heading = null;

    /**
     * @var Collection<int, Steps>
     */
    #[ORM\OneToMany(targetEntity: Steps::class, mappedBy: 'stepsSubheadings')]
    private Collection $steps;

    #[ORM\ManyToOne(inversedBy: 'stepsSubheadings')]
    private ?StepsTitle $stepsTitle = null;

    #[ORM\ManyToOne(inversedBy: 'stepsSubheadings')]
    private ?IluoLevels $iluoLevel = null;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function setHeading(?string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * @return Collection<int, Steps>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Steps $step): static
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setStepsSubheadings($this);
        }

        return $this;
    }

    public function removeStep(Steps $step): static
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getStepsSubheadings() === $this) {
                $step->setStepsSubheadings(null);
            }
        }

        return $this;
    }

    public function getStepsTitle(): ?StepsTitle
    {
        return $this->stepsTitle;
    }

    public function setStepsTitle(?StepsTitle $stepsTitle): static
    {
        $this->stepsTitle = $stepsTitle;

        return $this;
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
}
