<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[Broadcast]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: zone::class, inversedBy: 'roles')]
    private Collection $zone_id;

    #[ORM\OneToMany(mappedBy: 'role_id', targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->zone_id = new ArrayCollection();
        $this->users = new ArrayCollection();
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
     * @return Collection<int, zone>
     */
    public function getZoneId(): Collection
    {
        return $this->zone_id;
    }

    public function addZoneId(zone $zoneId): self
    {
        if (!$this->zone_id->contains($zoneId)) {
            $this->zone_id->add($zoneId);
        }

        return $this;
    }

    public function removeZoneId(zone $zoneId): self
    {
        $this->zone_id->removeElement($zoneId);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setRoleId($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getRoleId() === $this) {
                $user->setRoleId(null);
            }
        }

        return $this;
    }
}
