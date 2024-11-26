<?php

namespace App\Entity;

use App\Repository\ButtonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ButtonRepository::class)]
class Button
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'buttons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'button', targetEntity: Upload::class, orphanRemoval: true)]
    private Collection $uploads;

    #[ORM\OneToMany(mappedBy: 'button', targetEntity: OldUpload::class, orphanRemoval: true)]
    private Collection $olduploads;

    #[ORM\Column(nullable: true)]
    private ?int $SortOrder = null;

    #[ORM\ManyToOne(inversedBy: 'buttons')]
    private ?User $Creator = null;

    public function __construct()
    {
        $this->uploads = new ArrayCollection();
        $this->olduploads = new ArrayCollection();
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
            $upload->setButton($this);
        }

        return $this;
    }

    public function removeUpload(Upload $upload): self
    {
        if ($this->uploads->removeElement($upload)) {
            // set the owning side to null (unless already changed)
            if ($upload->getButton() === $this) {
                $upload->setButton(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Upload>
     */
    public function getOldUploads(): Collection
    {
        return $this->olduploads;
    }

    public function addOldUpload(OldUpload $oldupload): self
    {
        if (!$this->olduploads->contains($oldupload)) {
            $this->olduploads->add($oldupload);
            $oldupload->setButton($this);
        }

        return $this;
    }

    public function removeOldUpload(OldUpload $oldupload): self
    {
        if ($this->olduploads->removeElement($oldupload)) {
            // set the owning side to null (unless already changed)
            if ($oldupload->getButton() === $this) {
                $oldupload->setButton(null);
            }
        }

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->SortOrder;
    }

    public function setSortOrder(?int $SortOrder): static
    {
        $this->SortOrder = $SortOrder;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->Creator;
    }

    public function setCreator(?User $Creator): static
    {
        $this->Creator = $Creator;

        return $this;
    }
}