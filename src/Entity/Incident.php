<?php

namespace App\Entity;

use App\Repository\IncidentRepository;

use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

use Symfony\Component\HttpFoundation\File\File;


#[ORM\Entity(repositoryClass: IncidentRepository::class)]
#[Broadcast]
class Incident
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    private ?File $file = null;


    #[ORM\Column(length: 255)]
    private ?string $name = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploaded_at = null;

    #[ORM\ManyToOne(inversedBy: 'incidents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?IncidentCategory $IncidentCategory = null;

    #[ORM\ManyToOne(inversedBy: 'incidents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductLine $ProductLine = null;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\ManyToOne(inversedBy: 'incidents')]
    private ?User $Uploader = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIncidentCategory(): ?IncidentCategory
    {
        return $this->IncidentCategory;
    }

    public function setIncidentCategory(?IncidentCategory $IncidentCategory): self
    {
        $this->IncidentCategory = $IncidentCategory;

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

    public function getProductLine(): ?ProductLine
    {
        return $this->ProductLine;
    }

    public function setProductLine(?ProductLine $ProductLine): self
    {
        $this->ProductLine = $ProductLine;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

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

    public function getUploader(): ?User
    {
        return $this->Uploader;
    }

    public function setUploader(?User $Uploader): static
    {
        $this->Uploader = $Uploader;

        return $this;
    }
}