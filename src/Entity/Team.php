<?php

namespace App\Entity;

use App\Repository\TeamRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[UniqueEntity(fields: 'name', message: 'Une équipe avec ce nom existe déjà.')]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['operator_details'])]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Operator::class)]
    private Collection $operators;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex(
        pattern: '/^(?!-)(?!.*--)[A-Za-zÉÈÊËÀÂÄÔÖÙÛÜÇéèêëàâäôöùûüç][A-Za-zÉÈÊËÀÂÄÔÖÙÛÜÇéèêëàâäôöùûüç -]{2,}(?<!-)(?<! )$/',
        message: 'Format invalide. Veuillez saisir un nom d\'équipe d\'au moins 3 caractères, sans tirets consécutifs, sans tiret ou espace au début ou à la fin.'
    )]
    #[Groups(['operator_details'])]
    private ?string $name = null;

    public function __construct()
    {
        $this->operators = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $operator->setTeam($this);
        }

        return $this;
    }

    public function removeOperator(Operator $operator): static
    {
        if ($this->operators->removeElement($operator)) {
            // set the owning side to null (unless already changed)
            if ($operator->getTeam() === $this) {
                $operator->setTeam(null);
            }
        }

        return $this;
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
}
