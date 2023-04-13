<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

use App\Entity\ProductLine;
use App\Entity\Role;


#[ORM\Entity(repositoryClass: ZoneRepository::class)]
#[Broadcast]
class Zone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;


    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'zone_id')]
    private Collection $roles;

    #[ORM\OneToMany(mappedBy: 'zone', targetEntity: ProductLine::class, orphanRemoval: true)]
    private Collection $productLines;

    public function __construct()
    {
        $this->productLines = new ArrayCollection();
        $this->roles        = new ArrayCollection();
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
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addZoneId($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $role->removeZoneId($this);
        }

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
}