<?php

namespace App\Entity;

use App\Repository\OperatorsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OperatorsRepository::class)]
class Operators
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'operators')]
    private ?Team $Team = null;

    #[ORM\ManyToOne(inversedBy: 'operators')]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'operators')]
    private ?Uap $uap = null;

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

    public function getTeam(): ?Team
    {
        return $this->Team;
    }

    public function setTeam(?Team $Team): static
    {
        $this->Team = $Team;

        return $this;
    }

    public function getUap(): ?Uap
    {
        return $this->uap;
    }

    public function setUap(?Uap $uap): static
    {
        $this->uap = $uap;

        return $this;
    }
}
