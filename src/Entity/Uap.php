<?php

namespace App\Entity;

use App\Repository\UapRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UapRepository::class)]
class Uap
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'uap', targetEntity: Operators::class)]
    private Collection $operators;

    public function __construct()
    {
        $this->operators = new ArrayCollection();
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
     * @return Collection<int, Operators>
     */
    public function getOperators(): Collection
    {
        return $this->operators;
    }

    public function addOperator(Operators $operator): static
    {
        if (!$this->operators->contains($operator)) {
            $this->operators->add($operator);
            $operator->setUap($this);
        }

        return $this;
    }

    public function removeOperator(Operators $operator): static
    {
        if ($this->operators->removeElement($operator)) {
            // set the owning side to null (unless already changed)
            if ($operator->getUap() === $this) {
                $operator->setUap(null);
            }
        }

        return $this;
    }
}
