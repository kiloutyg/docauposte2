<?php

namespace App\Entity;

use App\Repository\OldUploadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: OldUploadRepository::class)]
class OldUpload
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiry_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $olduploaded_at = null;


    #[ORM\ManyToOne(inversedBy: 'olduploads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Button $button = null;

    #[ORM\ManyToOne(inversedBy: 'olduploads')]
    private ?DisplayOption $displayOption = null;

    #[ORM\Column(nullable: true)]
    private ?bool $validated = null;

    #[ORM\ManyToOne(inversedBy: 'olduploads')]
    private ?User $olduploader = null;

    #[ORM\Column(nullable: true)]
    private ?int $revision = null;

    #[ORM\OneToOne(mappedBy: 'OldUpload', cascade: ['persist', 'remove'])]
    private ?Upload $upload = null;


    #[ORM\OneToOne(inversedBy: 'oldupload', cascade: ['persist', 'remove'])]


    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            $this->olduploaded_at = new \DateTime();
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


    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiry_date;
    }

    public function setExpiryDate(?\DateTimeInterface $expiry_date): self
    {
        $this->expiry_date = $expiry_date;

        return $this;
    }

    public function getOldUploadedAt(): ?\DateTimeInterface
    {
        return $this->olduploaded_at;
    }

    public function setOldUploadedAt(\DateTimeInterface $olduploaded_at): self
    {
        $this->olduploaded_at = $olduploaded_at;

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


    public function getDisplayOption(): ?DisplayOption
    {
        return $this->displayOption;
    }

    public function setDisplayOption(?DisplayOption $displayOption): static
    {
        $this->displayOption = $displayOption;

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


    public function getOldUploader(): ?User
    {
        return $this->olduploader;
    }

    public function setOldUploader(?User $olduploader): static
    {
        $this->olduploader = $olduploader;

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

    public function getUpload(): ?Upload
    {
        return $this->upload;
    }

    public function setUpload(?Upload $upload): static
    {
        // unset the owning side of the relation if necessary
        if ($upload === null && $this->upload !== null) {
            $this->upload->setOldUpload(null);
        }

        // set the owning side of the relation if necessary
        if ($upload !== null && $upload->getOldUpload() !== $this) {
            $upload->setOldUpload($this);
        }

        $this->upload = $upload;

        return $this;
    }
}