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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trainingMaterialType = null;

    /**
     * @var Collection<int, Upload>
     */
    #[ORM\ManyToMany(targetEntity: Upload::class, inversedBy: 'steps')]
    private Collection $uploads;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    private ?StepsSubheadings $subheading = null;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    private ?StepsTitle $title = null;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    private ?IluoLevels $iluoLevel = null;

    /**
     * @var Collection<int, IluoChecklist>
     */
    #[ORM\ManyToMany(targetEntity: IluoChecklist::class, mappedBy: 'step')]
    private Collection $iluoChecklists;

    public function __construct()
    {
        $this->uploads = new ArrayCollection();
        $this->iluoChecklists = new ArrayCollection();
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

    public function getTrainingMaterialType(): ?string
    {
        return $this->trainingMaterialType;
    }

    public function setTrainingMaterialType(?string $trainingMaterialType): static
    {
        $this->trainingMaterialType = $trainingMaterialType;

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

    public function getSubheading(): ?StepsSubheadings
    {
        return $this->subheading;
    }

    public function setSubheading(?StepsSubheadings $subheading): static
    {
        $this->subheading = $subheading;

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
}
