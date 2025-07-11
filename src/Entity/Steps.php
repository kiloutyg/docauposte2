<?php

namespace App\Entity;

use App\Repository\StepsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StepsRepository::class)]
class Steps
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $question = null;

    /**
     * @var Collection<int, Upload>
     */
    #[ORM\ManyToMany(targetEntity: Upload::class, inversedBy: 'steps')]
    private Collection $uploads;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    private ?StepsSubheadings $stepsSubheadings = null;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    private ?StepsTitle $stepsTitle = null;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    private ?IluoLevels $iluoLevel = null;

    /**
     * @var Collection<int, IluoChecklist>
     */
    #[ORM\ManyToMany(targetEntity: IluoChecklist::class, mappedBy: 'step')]
    private Collection $iluoChecklists;

    /**
     * @var Collection<int, TrainingMaterialType>
     */
    #[ORM\ManyToMany(targetEntity: TrainingMaterialType::class, inversedBy: 'steps')]
    private Collection $trainingMaterialType;

    public function __construct()
    {
        $this->uploads = new ArrayCollection();
        $this->iluoChecklists = new ArrayCollection();
        $this->trainingMaterialType = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): static
    {
        $this->question = $question;

        return $this;
    }
    
    /**
     * @return Collection<int, Upload>
     */
    public function getUploads(): Collection
    {
        return $this->uploads;
    }

    public function addUpload(Upload $upload): static
    {
        if (!$this->uploads->contains($upload)) {
            $this->uploads->add($upload);
        }

        return $this;
    }

    public function removeUpload(Upload $upload): static
    {
        $this->uploads->removeElement($upload);

        return $this;
    }

    public function getStepsSubheadings(): ?StepsSubheadings
    {
        return $this->stepsSubheadings;
    }

    public function setStepsSubheadings(?StepsSubheadings $stepsSubheadings): static
    {
        $this->stepsSubheadings = $stepsSubheadings;

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
            $iluoChecklist->addStep($this);
        }

        return $this;
    }

    public function removeIluoChecklist(IluoChecklist $iluoChecklist): static
    {
        if ($this->iluoChecklists->removeElement($iluoChecklist)) {
            $iluoChecklist->removeStep($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TrainingMaterialType>
     */
    public function getTrainingMaterialType(): Collection
    {
        return $this->trainingMaterialType;
    }

    public function addTrainingMaterialType(TrainingMaterialType $trainingMaterialType): static
    {
        if (!$this->trainingMaterialType->contains($trainingMaterialType)) {
            $this->trainingMaterialType->add($trainingMaterialType);
        }

        return $this;
    }

    public function removeTrainingMaterialType(TrainingMaterialType $trainingMaterialType): static
    {
        $this->trainingMaterialType->removeElement($trainingMaterialType);

        return $this;
    }
}
