<?php

namespace App\Entity;

use App\Model\TrainingMaterialTypeCategory;
use App\Repository\TrainingMaterialTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrainingMaterialTypeRepository::class)]
class TrainingMaterialType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Steps>
     */
    #[ORM\ManyToMany(targetEntity: Steps::class, mappedBy: 'trainingMaterialType')]
    private Collection $steps;

    #[ORM\Column(enumType: TrainingMaterialTypeCategory::class)]
    private ?TrainingMaterialTypeCategory $category = null;

    #[ORM\OneToOne(inversedBy: 'trainingMaterialType', cascade: ['persist', 'remove'])]
    private ?Upload $upload = null;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $step->addTrainingMaterialType($this);
        }

        return $this;
    }

    public function removeStep(Steps $step): static
    {
        if ($this->steps->removeElement($step)) {
            $step->removeTrainingMaterialType($this);
        }

        return $this;
    }

    public function getCategory(): ?TrainingMaterialTypeCategory
    {
        return $this->category;
    }

    public function setCategory(TrainingMaterialTypeCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getUpload(): ?Upload
    {
        return $this->upload;
    }

    public function setUpload(?Upload $upload): static
    {
        $this->upload = $upload;

        return $this;
    }
}
