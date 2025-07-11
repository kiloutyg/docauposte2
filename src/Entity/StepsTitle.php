<?php

namespace App\Entity;

use App\Repository\StepsTitleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StepsTitleRepository::class)]
class StepsTitle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    /**
     * @var Collection<int, Steps>
     */
    #[ORM\OneToMany(targetEntity: Steps::class, mappedBy: 'stepsTitle')]
    private Collection $steps;

    /**
     * @var Collection<int, StepsSubheadings>
     */
    #[ORM\OneToMany(targetEntity: StepsSubheadings::class, mappedBy: 'stepsTitle')]
    private Collection $stepsSubheadings;

    #[ORM\ManyToOne(inversedBy: 'stepsTitles')]
    private ?IluoLevels $iluoLevel = null;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->stepsSubheadings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

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
            $step->setStepsTitle($this);
        }

        return $this;
    }

    public function removeStep(Steps $step): static
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getStepsTitle() === $this) {
                $step->setStepsTitle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StepsSubheadings>
     */
    public function getStepsSubheadings(): Collection
    {
        return $this->stepsSubheadings;
    }

    public function addStepsSubheading(StepsSubheadings $stepsSubheading): static
    {
        if (!$this->stepsSubheadings->contains($stepsSubheading)) {
            $this->stepsSubheadings->add($stepsSubheading);
            $stepsSubheading->setStepsTitle($this);
        }

        return $this;
    }

    public function removeStepsSubheading(StepsSubheadings $stepsSubheading): static
    {
        if ($this->stepsSubheadings->removeElement($stepsSubheading)) {
            // set the owning side to null (unless already changed)
            if ($stepsSubheading->getStepsTitle() === $this) {
                $stepsSubheading->setStepsTitle(null);
            }
        }

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
