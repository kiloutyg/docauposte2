<?php

namespace App\Entity;

use App\Repository\UploadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

use App\Entity\OldUpload;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UploadRepository::class)]
#[Table(name: 'upload')]
#[UniqueConstraint(name: 'unique_filename_by_button', columns: ['filename', 'button_id'])]
class Upload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    private ?int $id = null;

    private ?File $file = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex(pattern: "/^[\p{L}0-9()][\p{L}0-9()_.'-]{2,253}[\p{L}0-9]$/mu", message: 'Format de nom de fichier invalide. Utilisez uniquement des lettres, chiffres, parenthèses, tirets, points et underscores. Le nom ne doit pas commencer ou finir par un point ou un tiret. Le nom doit être unique.')]

    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploadedAt = null;

    #[ORM\ManyToOne(inversedBy: 'uploads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Button $button = null;

    #[ORM\Column(nullable: true)]
    private ?bool $validated = null;

    #[ORM\OneToOne(mappedBy: 'Upload', cascade: ['persist', 'remove'])]
    private ?Validation $validation = null;

    #[ORM\ManyToOne(inversedBy: 'uploads')]
    private ?User $uploader = null;

    #[ORM\Column(nullable: true)]
    private ?int $revision = null;

    #[ORM\OneToOne(inversedBy: 'upload', cascade: ['persist', 'remove'])]
    private ?OldUpload $oldUpload = null;

    #[ORM\OneToMany(mappedBy: 'upload', cascade: ['persist', 'remove'], targetEntity: TrainingRecord::class)]
    private Collection $trainingRecords;

    #[ORM\Column(nullable: true)]
    private ?bool $training = null;

    #[ORM\Column(nullable: true)]
    private ?bool $forcedDisplay = null;

    /**
     * @var Collection<int, Steps>
     */
    #[ORM\ManyToMany(targetEntity: Steps::class, mappedBy: 'uploads')]
    private Collection $steps;

    /**
     * @var Collection<int, Workstation>
     */
    #[ORM\OneToMany(targetEntity: Workstation::class, mappedBy: 'upload')]
    private Collection $workstations;

    public function __construct()
    {
        $this->trainingRecords = new ArrayCollection();
        $this->steps = new ArrayCollection();
        $this->workstations = new ArrayCollection();
    }


    #[ORM\OneToOne(inversedBy: 'upload', cascade: ['persist', 'remove'])]


    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            $this->uploadedAt = new \DateTime();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeInterface $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getButton(): ?Button
    {
        return $this->button;
    }

    public function setButton(?Button $button): self
    {
        $this->button = $button;

        return $this;
    }

    public function isValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(?bool $validated): static
    {
        $this->validated = $validated;

        return $this;
    }

    public function getValidation(): ?Validation
    {
        return $this->validation;
    }

    public function setValidation(Validation $validation): static
    {
        // set the owning side of the relation if necessary
        if ($validation->getUpload() !== $this) {
            $validation->setUpload($this);
        }

        $this->validation = $validation;

        return $this;
    }

    public function getUploader(): ?User
    {
        return $this->uploader;
    }

    public function setUploader(?User $uploader): static
    {
        $this->uploader = $uploader;

        return $this;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(?int $revision): static
    {
        $this->revision = $revision;

        return $this;
    }

    public function getOldUpload(): ?OldUpload
    {
        return $this->oldUpload;
    }

    public function setOldUpload(?OldUpload $oldUpload): static
    {
        $this->oldUpload = $oldUpload;

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
            $trainingRecord->setUpload($this);
        }

        return $this;
    }

    public function removeTrainingRecord(TrainingRecord $trainingRecord): static
    {
        if ($this->trainingRecords->removeElement($trainingRecord)) {
            // set the owning side to null (unless already changed)
            if ($trainingRecord->getUpload() === $this) {
                $trainingRecord->setUpload(null);
            }
        }

        return $this;
    }

    public function isTraining(): ?bool
    {
        return $this->training;
    }

    public function setTraining(?bool $training): static
    {
        $this->training = $training;

        return $this;
    }

    public function isForcedDisplay(): ?bool
    {
        return $this->forcedDisplay;
    }

    public function setForcedDisplay(?bool $forcedDisplay): static
    {
        $this->forcedDisplay = $forcedDisplay;

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
            $step->addUpload($this);
        }

        return $this;
    }

    public function removeStep(Steps $step): static
    {
        if ($this->steps->removeElement($step)) {
            $step->removeUpload($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Workstation>
     */
    public function getWorkstations(): Collection
    {
        return $this->workstations;
    }

    public function addWorkstation(Workstation $workstation): static
    {
        if (!$this->workstations->contains($workstation)) {
            $this->workstations->add($workstation);
            $workstation->setUpload($this);
        }

        return $this;
    }

    public function removeWorkstation(Workstation $workstation): static
    {
        if ($this->workstations->removeElement($workstation)) {
            // set the owning side to null (unless already changed)
            if ($workstation->getUpload() === $this) {
                $workstation->setUpload(null);
            }
        }

        return $this;
    }
}
