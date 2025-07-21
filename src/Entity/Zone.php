<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\ProductLine;


#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'zone', targetEntity: ProductLine::class)]
    private Collection $productLines;

    #[ORM\Column(nullable: true)]
    private ?int $SortOrder = null;

    #[ORM\ManyToOne(inversedBy: 'zones')]
    private ?User $Creator = null;

    /**
     * @var Collection<int, Workstation>
     */
    #[ORM\OneToMany(targetEntity: Workstation::class, mappedBy: 'zone')]
    private Collection $workstations;

    #[ORM\ManyToOne(inversedBy: 'zones')]
    private ?Department $department = null;

    public function __construct()
    {
        $this->productLines = new ArrayCollection();
        $this->workstations = new ArrayCollection();
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

    /**
     * @return Collection<int, ProductLine>
     */
    public function getProductLines(): Collection
    {
        return $this->productLines;
    }

    public function addProductLines(ProductLine $productLines): self
    {
        if (!$this->productLines->contains($productLines)) {
            $this->productLines->add($productLines);
            $productLines->setZone($this);
        }

        return $this;
    }

    public function removeProductLines(ProductLine $productLines): self
    {
        if ($this->productLines->removeElement($productLines)) {
            // set the owning side to null (unless already changed)
            if ($productLines->getZone() === $this) {
                $productLines->setZone(null);
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
            $workstation->setZone($this);
        }

        return $this;
    }

    public function removeWorkstation(Workstation $workstation): static
    {
        if ($this->workstations->removeElement($workstation)) {
            // set the owning side to null (unless already changed)
            if ($workstation->getZone() === $this) {
                $workstation->setZone(null);
            }
        }

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }
}
