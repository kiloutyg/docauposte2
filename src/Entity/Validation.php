<?php

namespace App\Entity;

use App\Repository\ValidationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValidationRepository::class)]
class Validation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'validation', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Upload $Upload = null;

    #[ORM\Column(nullable: true)]
    private ?bool $status = null;

    #[ORM\OneToMany(mappedBy: 'Validation', targetEntity: Approbation::class, orphanRemoval: true)]
    private Collection $approbations;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validated_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Comment = null;

    public function __construct()
    {
        $this->approbations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpload(): ?Upload
    {
        return $this->Upload;
    }

    public function setUpload(Upload $Upload): static
    {
        $this->Upload = $Upload;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Approbation>
     */
    public function getApprobations(): Collection
    {
        return $this->approbations;
    }

    public function addApprobation(Approbation $approbation): static
    {
        if (!$this->approbations->contains($approbation)) {
            $this->approbations->add($approbation);
            $approbation->setValidation($this);
        }

        return $this;
    }

    public function removeApprobation(Approbation $approbation): static
    {
        if ($this->approbations->removeElement($approbation)) {
            // set the owning side to null (unless already changed)
            if ($approbation->getValidation() === $this) {
                $approbation->setValidation(null);
            }
        }

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validated_at;
    }

    public function setValidatedAt(?\DateTimeInterface $validated_at): static
    {
        $this->validated_at = $validated_at;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->Comment;
    }

    public function setComment(?string $Comment): static
    {
        $this->Comment = $Comment;

        return $this;
    }
}
