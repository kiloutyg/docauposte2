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

    #[ORM\Column(length: 255, nullable: false, unique: true)]
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
    private Collection $uaps;

    /**
     * @var Collection<int, Zone>
     */
    #[ORM\OneToMany(targetEntity: Zone::class, mappedBy: 'department')]
    private Collection $zones;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->approbations = new ArrayCollection();
        $this->workstations = new ArrayCollection();
        $this->uaps = new ArrayCollection();
        $this->zones = new ArrayCollection();
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
    public function getUaps(): Collection
    {
        return $this->uaps;
    }

    public function addUap(Uap $uaps): static
    {
        if (!$this->uaps->contains($uaps)) {
            $this->uaps->add($uaps);
            $uaps->setDepartment($this);
        }

        return $this;
    }

    public function removeUap(Uap $uaps): static
    {
        if ($this->uaps->removeElement($uaps)) {
            // set the owning side to null (unless already changed)
            if ($uaps->getDepartment() === $this) {
                $uaps->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Zone>
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $zones): static
    {
        if (!$this->zones->contains($zones)) {
            $this->zones->add($zones);
            $zones->setDepartment($this);
        }

        return $this;
    }

    public function removeZone(Zone $zones): static
    {
        if ($this->zones->removeElement($zones)) {
            // set the owning side to null (unless already changed)
            if ($zones->getDepartment() === $this) {
                $zones->setDepartment(null);
            }
        }

        return $this;
    }
}
