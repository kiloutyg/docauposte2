<?php

namespace App\Entity;

use App\Repository\ProductLineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

use App\Entity\Zone;
use App\Entity\Document;


#[ORM\Entity(repositoryClass: ProductLineRepository::class)]
#[Broadcast]
class ProductLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'productLines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?zone $zone = null;

    #[ORM\OneToMany(mappedBy: 'productline', targetEntity: Document::class, orphanRemoval: true)]
    private Collection $documents;

    #[ORM\OneToMany(mappedBy: 'productline', targetEntity: Upload::class)]
    private Collection $uploads;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->uploads = new ArrayCollection();
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


    public function getZone(): ?zone
    {
        return $this->zone;
    }

    public function setZone(?zone $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocuments(Document $documents): self
    {
        if (!$this->documents->contains($documents)) {
            $this->documents->add($documents);
            $documents->setProductline($this);
        }

        return $this;
    }

    public function removeDocuments(Document $documents): self
    {
        if ($this->documents->removeElement($documents)) {
            // set the owning side to null (unless already changed)
            if ($documents->getProductline() === $this) {
                $documents->setProductline(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Upload>
     */
    public function getUploads(): Collection
    {
        return $this->uploads;
    }

    public function addUpload(Upload $upload): self
    {
        if (!$this->uploads->contains($upload)) {
            $this->uploads->add($upload);
            $upload->setProductline($this);
        }

        return $this;
    }

    public function removeUpload(Upload $upload): self
    {
        if ($this->uploads->removeElement($upload)) {
            // set the owning side to null (unless already changed)
            if ($upload->getProductline() === $this) {
                $upload->setProductline(null);
            }
        }

        return $this;
    }
}