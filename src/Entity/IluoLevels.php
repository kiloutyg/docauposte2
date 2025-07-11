<?php

namespace App\Entity;

use App\Repository\IluoLevelsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IluoLevelsRepository::class)]
class IluoLevels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $level = null;

    /**
     * @var Collection<int, Steps>
     */
    #[ORM\OneToMany(targetEntity: Steps::class, mappedBy: 'iluoLevel')]
    private Collection $steps;

    /**
     * @var Collection<int, StepsSubheadings>
     */
    #[ORM\OneToMany(targetEntity: StepsSubheadings::class, mappedBy: 'iluoLevel')]
    private Collection $stepsSubheadings;

    /**
     * @var Collection<int, StepsTitle>
     */
    #[ORM\OneToMany(targetEntity: StepsTitle::class, mappedBy: 'iluoLevel')]
    private Collection $stepsTitles;

    /**
     * @var Collection<int, IluoChecklist>
     */
    #[ORM\OneToMany(targetEntity: IluoChecklist::class, mappedBy: 'iluoLevel')]
    private Collection $iluoChecklists;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->stepsSubheadings = new ArrayCollection();
        $this->stepsTitles = new ArrayCollection();
        $this->iluoChecklists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $level;

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
            $step->setIluoLevel($this);
        }

        return $this;
    }

    public function removeStep(Steps $step): static
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getIluoLevel() === $this) {
                $step->setIluoLevel(null);
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
            $stepsSubheading->setIluoLevel($this);
        }

        return $this;
    }

    public function removeStepsSubheading(StepsSubheadings $stepsSubheading): static
    {
        if ($this->stepsSubheadings->removeElement($stepsSubheading)) {
            // set the owning side to null (unless already changed)
            if ($stepsSubheading->getIluoLevel() === $this) {
                $stepsSubheading->setIluoLevel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StepsTitle>
     */
    public function getStepsTitles(): Collection
    {
        return $this->stepsTitles;
    }

    public function addStepsTitle(StepsTitle $stepsTitle): static
    {
        if (!$this->stepsTitles->contains($stepsTitle)) {
            $this->stepsTitles->add($stepsTitle);
            $stepsTitle->setIluoLevel($this);
        }

        return $this;
    }

    public function removeStepsTitle(StepsTitle $stepsTitle): static
    {
        if ($this->stepsTitles->removeElement($stepsTitle)) {
            // set the owning side to null (unless already changed)
            if ($stepsTitle->getIluoLevel() === $this) {
                $stepsTitle->setIluoLevel(null);
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
            $iluoChecklist->setIluoLevel($this);
        }

        return $this;
    }

    public function removeIluoChecklist(IluoChecklist $iluoChecklist): static
    {
        if ($this->iluoChecklists->removeElement($iluoChecklist)) {
            // set the owning side to null (unless already changed)
            if ($iluoChecklist->getIluoLevel() === $this) {
                $iluoChecklist->setIluoLevel(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
