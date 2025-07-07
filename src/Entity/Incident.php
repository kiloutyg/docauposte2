<?php

namespace App\Entity;

use App\Repository\IncidentRepository;

use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IncidentRepository::class)]
#[Table(name: 'incident')]
#[UniqueConstraint(name: 'unique_name_by_product_line', columns: ['name', 'product_line_id'])]
class Incident
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    private ?File $file = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Assert\Regex(pattern: "/^[\p{L}0-9()][\p{L}0-9()_.'-]{2,253}[\p{L}0-9()]$/mu", message: 'Format de nom de fichier invalide. Doit commencer et finir par une lettre/chiffre/parenthèse. Caractères autorisés : lettres, chiffres, ()_.\'-')]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploadedAt = null;

    #[ORM\ManyToOne(inversedBy: 'incidents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?IncidentCategory $IncidentCategory = null;

    #[ORM\ManyToOne(inversedBy: 'incidents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductLine $productLine = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\ManyToOne(inversedBy: 'incidents')]
    private ?User $Uploader = null;

    #[ORM\Column(nullable: true)]
    private ?bool $ActivateAutoDisplay = null;

    #[ORM\Column(nullable: true)]
    private ?int $AutoDisplayPriority = null;

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
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeInterface $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getProductLine(): ?ProductLine
    {
        return $this->productLine;
    }

    public function setProductLine(?ProductLine $productLine): self
    {
        $this->productLine = $productLine;

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

    public function isActivateAutoDisplay(): ?bool
    {
        return $this->ActivateAutoDisplay;
    }

    public function setActivateAutoDisplay(?bool $ActivateAutoDisplay): static
    {
        $this->ActivateAutoDisplay = $ActivateAutoDisplay;

        return $this;
    }

    public function getAutoDisplayPriority(): ?int
    {
        return $this->AutoDisplayPriority;
    }

    public function setAutoDisplayPriority(?int $AutoDisplayPriority): static
    {
        $this->AutoDisplayPriority = $AutoDisplayPriority;

        return $this;
    }
}
