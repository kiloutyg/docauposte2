<?php

namespace App\Entity;

use App\Repository\UploadRepository;
use App\Repository\OldUploadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

use App\Entity\OldUpload;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: UploadRepository::class)]
class Upload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    private ?int $id = null;

    private ?File $file = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploaded_at = null;

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
    private ?OldUpload $OldUpload = null;

    #[ORM\OneToMany(mappedBy: 'Upload', cascade: ['persist', 'remove'], targetEntity: TrainingRecord::class)]
    private Collection $trainingRecords;

    #[ORM\OneToMany(mappedBy: 'upload', targetEntity: Trainer::class)]
    private Collection $trainers;

    #[ORM\Column(nullable: true)]
    private ?bool $training = null;

    #[ORM\Column(nullable: true)]
    private ?bool $forcedDisplay = null;

    public function __construct()
    {
        $this->trainingRecords = new ArrayCollection();
        $this->trainers = new ArrayCollection();
    }


    #[ORM\OneToOne(inversedBy: 'upload', cascade: ['persist', 'remove'])]


    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            $this->uploaded_at = new \DateTime();
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
        return $this->uploaded_at;
    }

    public function setUploadedAt(\DateTimeInterface $uploaded_at): self
    {
        $this->uploaded_at = $uploaded_at;

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
        return $this->OldUpload;
    }

    public function setOldUpload(?OldUpload $OldUpload): static
    {
        $this->OldUpload = $OldUpload;

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

    /**
     * @return Collection<int, Trainer>
     */
    public function getTrainers(): Collection
    {
        return $this->trainers;
    }

    public function addTrainer(Trainer $trainer): static
    {
        if (!$this->trainers->contains($trainer)) {
            $this->trainers->add($trainer);
            $trainer->setUpload($this);
        }

        return $this;
    }

    public function removeTrainer(Trainer $trainer): static
    {
        if ($this->trainers->removeElement($trainer)) {
            // set the owning side to null (unless already changed)
            if ($trainer->getUpload() === $this) {
                $trainer->setUpload(null);
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
}
