<?php

namespace App\Entity;

use App\Repository\UapRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UapRepository::class)]
class Uap
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['operator_details'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['operator_details'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Operator>
     */
    #[ORM\ManyToMany(targetEntity: Operator::class, inversedBy: 'uaps')]
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
     * @return Collection<int, Operator>
     */
    public function getOperators(): Collection
    {
        return $this->operators;
    }

    public function addOperator(Operator $operator): static
    {
        if (!$this->operators->contains($operator)) {
            $this->operators->add($operator);
        }

        return $this;
    }

    public function removeOperator(Operator $operator): static
    {
        $this->operators->removeElement($operator);

        return $this;
    }
}
