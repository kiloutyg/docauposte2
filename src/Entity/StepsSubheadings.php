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
    #[ORM\OneToMany(targetEntity: Steps::class, mappedBy: 'subheading')]
    private Collection $steps;

    #[ORM\ManyToOne(inversedBy: 'stepsSubheadings')]
    private ?StepsTitle $title = null;

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
            $step->setSubheading($this);
        }

        return $this;
    }

    public function removeStep(Steps $step): static
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getSubheading() === $this) {
                $step->setSubheading(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?StepsTitle
    {
        return $this->title;
    }

    public function setTitle(?StepsTitle $title): static
    {
        $this->title = $title;

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
