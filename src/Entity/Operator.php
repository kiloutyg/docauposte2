<?php

namespace App\Entity;

use App\Repository\OperatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OperatorRepository::class)]
class Operator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex(pattern: '/^[a-zA-Z]+\.[a-zA-Z]+$/', message: 'Le nom d\'utilisateur doit être au format prénom.nom')]

    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'operators')]
    private ?Team $Team = null;

    #[ORM\ManyToOne(inversedBy: 'operators')]
    private ?Uap $uap = null;

    #[ORM\OneToMany(mappedBy: 'operator', targetEntity: TrainingRecord::class)]
    private Collection $trainingRecords;

    public function __construct()
    {
        $this->trainingRecords = new ArrayCollection();
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

    /**
     * @return Collection<int, TrainingRecord>
     */
    public function getTrainingRecords(): Collection
    {
        return $this->trainingRecords;
    }

    public function addTrainingRecord(TrainingRecord $trainingRecord): static
    {
        if (!$this->trainingRecords->contains($trainingRecord)) {
            $this->trainingRecords->add($trainingRecord);
            $trainingRecord->setOperator($this);
        }

        return $this;
    }

    public function removeTrainingRecord(TrainingRecord $trainingRecord): static
    {
        if ($this->trainingRecords->removeElement($trainingRecord)) {
            // set the owning side to null (unless already changed)
            if ($trainingRecord->getOperator() === $this) {
                $trainingRecord->setOperator(null);
            }
        }

        return $this;
    }
}
