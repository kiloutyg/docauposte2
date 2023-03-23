<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Broadcast]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $hashed_password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?role $role_id = null;

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

    public function getHashedPassword(): ?string
    {
        return $this->hashed_password;
    }

    public function setHashedPassword(string $hashed_password): self
    {
        $this->hashed_password = $hashed_password;

        return $this;
    }

    public function getRoleId(): ?role
    {
        return $this->role_id;
    }

    public function setRoleId(?role $role_id): self
    {
        $this->role_id = $role_id;

        return $this;
    }
}
