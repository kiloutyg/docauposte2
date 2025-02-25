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

    #[ORM\OneToMany(mappedBy: 'uap', targetEntity: Operator::class)]
    private Collection $operators;

    /**
     * @var Collection<int, Operator>
     */
    #[ORM\ManyToMany(targetEntity: Operator::class, inversedBy: 'uaps')]
    private Collection $Ope;

    public function __construct()
    {
        $this->operators = new ArrayCollection();
        $this->Ope = new ArrayCollection();
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
    public function getOperator(): Collection
    {
        return $this->operators;
    }

    public function addOperator(Operator $operator): static
    {
        if (!$this->operators->contains($operator)) {
            $this->operators->add($operator);
            $operator->setUap($this);
        }

        return $this;
    }

    public function removeOperator(Operator $operator): static
    {
        if ($this->operators->removeElement($operator)) {
            // set the owning side to null (unless already changed)
            if ($operator->getUap() === $this) {
                $operator->setUap(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Operator>
     */
    public function getOpe(): Collection
    {
        return $this->Ope;
    }

    public function addOpe(Operator $ope): static
    {
        if (!$this->Ope->contains($ope)) {
            $this->Ope->add($ope);
        }

        return $this;
    }

    public function removeOpe(Operator $ope): static
    {
        $this->Ope->removeElement($ope);

        return $this;
    }
}
