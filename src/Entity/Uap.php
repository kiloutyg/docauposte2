<?php

namespace App\Entity;

use App\Repository\UapRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: UapRepository::class)]
#[UniqueEntity(fields: 'name', message: 'Une UAP avec ce nom existe déjà.')]
class Uap
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['operator_details'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex(pattern: '/^(?!-)(?!.*--)[A-Z-]{3,}(?<!-)$/', message: 'Format invalide. Veuillez saisir sous la forme UAP')]
    #[Groups(['operator_details'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Operator>
     */
    #[ORM\ManyToMany(targetEntity: Operator::class, inversedBy: 'uaps')]
    private Collection $operators;

    #[ORM\ManyToOne(inversedBy: 'uaps')]
    private ?Department $department = null;

    /**
     * @var Collection<int, Workstation>
     */
    #[ORM\OneToMany(targetEntity: Workstation::class, mappedBy: 'uap')]
    private Collection $workstations;

    public function __construct()
    {
        $this->operators = new ArrayCollection();
        $this->workstations = new ArrayCollection();
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

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

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
            $workstation->setUap($this);
        }

        return $this;
    }

    public function removeWorkstation(Workstation $workstation): static
    {
        if ($this->workstations->removeElement($workstation)) {
            // set the owning side to null (unless already changed)
            if ($workstation->getUap() === $this) {
                $workstation->setUap(null);
            }
        }

        return $this;
    }
}
