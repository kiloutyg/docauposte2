<?php

namespace App\Entity;

use App\Repository\UploadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiry_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploaded_at = null;


    #[ORM\ManyToOne(inversedBy: 'uploads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Button $button = null;

    #[ORM\ManyToOne(inversedBy: 'uploads')]
    private ?DisplayOption $displayOption = null;

    #[ORM\Column(nullable: true)]
    private ?bool $validated = null;

    #[ORM\OneToOne(mappedBy: 'Upload', cascade: ['persist', 'remove'])]
    private ?Validation $validation = null;

    #[ORM\ManyToOne(inversedBy: 'uploads')]
    private ?User $uploader = null;





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

    // #[ORM\PrePersist]
    // #[ORM\PreUpdate]

    // public function updatePath(): void
    // {
    //     // This is just an example, adjust it according to your actual directory structure.

    //     $this->path = '/var/www/public/doc/' . $this->filename;
    // }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiry_date;
    }

    public function setExpiryDate(?\DateTimeInterface $expiry_date): self
    {
        $this->expiry_date = $expiry_date;

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

    // /**
    //  * @return Collection<int, Incident>
    //  */
    // public function getIncidents(): Collection
    // {
    //     return $this->incidents;
    // }

    // public function addIncident(Incident $incident): self
    // {
    //     if (!$this->incidents->contains($incident)) {
    //         $this->incidents->add($incident);
    //         $incident->setUpload($this);
    //     }

    //     return $this;
    // }

    // public function removeIncident(Incident $incident): self
    // {
    //     if ($this->incidents->removeElement($incident)) {
    //         // set the owning side to null (unless already changed)
    //         if ($incident->getUpload() === $this) {
    //             $incident->setUpload(null);
    //         }
    //     }

    //     return $this;
    // }

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
}