<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'DepartmentApprobator', targetEntity: Approbation::class)]
    private Collection $approbations;

    /**
     * @var Collection<int, Workstation>
     */
    #[ORM\OneToMany(targetEntity: Workstation::class, mappedBy: 'department')]
    private Collection $workstations;

    /**
     * @var Collection<int, Uap>
     */
    #[ORM\OneToMany(targetEntity: Uap::class, mappedBy: 'department')]
    private Collection $uap;

    /**
     * @var Collection<int, Zone>
     */
    #[ORM\OneToMany(targetEntity: Zone::class, mappedBy: 'department')]
    private Collection $zone;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->approbations = new ArrayCollection();
        $this->workstations = new ArrayCollection();
        $this->uap = new ArrayCollection();
        $this->zone = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setDepartment($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getDepartment() === $this) {
                $user->setDepartment(null);
            }
        }

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
            $approbation->setDepartmentApprobator($this);
        }

        return $this;
    }

    public function removeApprobation(Approbation $approbation): static
    {
        if ($this->approbations->removeElement($approbation)) {
            // set the owning side to null (unless already changed)
            if ($approbation->getDepartmentApprobator() === $this) {
                $approbation->setDepartmentApprobator(null);
            }
        }

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
            $workstation->setDepartment($this);
        }

        return $this;
    }

    public function removeWorkstation(Workstation $workstation): static
    {
        if ($this->workstations->removeElement($workstation)) {
            // set the owning side to null (unless already changed)
            if ($workstation->getDepartment() === $this) {
                $workstation->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Uap>
     */
    public function getUap(): Collection
    {
        return $this->uap;
    }

    public function addUap(Uap $uap): static
    {
        if (!$this->uap->contains($uap)) {
            $this->uap->add($uap);
            $uap->setDepartment($this);
        }

        return $this;
    }

    public function removeUap(Uap $uap): static
    {
        if ($this->uap->removeElement($uap)) {
            // set the owning side to null (unless already changed)
            if ($uap->getDepartment() === $this) {
                $uap->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Zone>
     */
    public function getZone(): Collection
    {
        return $this->zone;
    }

    public function addZone(Zone $zone): static
    {
        if (!$this->zone->contains($zone)) {
            $this->zone->add($zone);
            $zone->setDepartment($this);
        }

        return $this;
    }

    public function removeZone(Zone $zone): static
    {
        if ($this->zone->removeElement($zone)) {
            // set the owning side to null (unless already changed)
            if ($zone->getDepartment() === $this) {
                $zone->setDepartment(null);
            }
        }

        return $this;
    }
}