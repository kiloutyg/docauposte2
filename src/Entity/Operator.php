<?php

namespace App\Entity;

use App\Model\EmploymentType;
use App\Repository\OperatorRepository;
use App\Validator\OperatorCodeFormat;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: OperatorRepository::class)]
#[UniqueEntity(fields: 'code', message: 'Un opérateur avec ce code existe déjà.')]
#[UniqueEntity(fields: 'name', message: 'Un opérateur avec ce nom existe déjà.')]
class Operator
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex(pattern: '/^(?!-)(?!.*--)[a-zA-Z-]+(?<!-)\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/', message: 'Le nom d\'opérateur doit être au format prénom.nom')]
    #[Groups(['operator_details'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'operators')]

    #[Groups(['operator_details'])]
    #[Assert\NotNull]
    private ?Team $team = null;

    #[ORM\OneToMany(mappedBy: 'operator', targetEntity: TrainingRecord::class)]
    private Collection $trainingRecords;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[OperatorCodeFormat]
    #[Groups(['operator_details'])]
    private ?string $code = null;

    #[ORM\OneToOne(mappedBy: 'operator', cascade: ['remove'])]
    private ?Trainer $trainer = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['operator_details'])]
    private ?bool $isTrainer = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lasttraining = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $tobedeleted = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $inactiveSince = null;


    /**
     * @var Collection<int, Uap>
     */
    #[ORM\ManyToMany(targetEntity: Uap::class, mappedBy: 'operators')]
    #[Assert\NotNull]
    #[Assert\Count(min: 1, minMessage: "Un opérateur doit être assigné à au moins une UAP")]
    private Collection $uaps;

    /**
     * @var Collection<int, Iluo>
     */
    #[ORM\OneToMany(targetEntity: Iluo::class, mappedBy: 'operator')]
    private Collection $iluos;

    #[ORM\Column(nullable: true, enumType: EmploymentType::class)]
    private ?EmploymentType $employmentType = null;

    #[ORM\OneToOne(mappedBy: 'operator', cascade: ['persist'])]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'operator', cascade: ['persist', 'remove'])]
    private ?ShiftLeaders $shiftLeaders = null;


    public function __construct()
    {
        $this->trainingRecords = new ArrayCollection();
        $this->uaps = new ArrayCollection();
        $this->iluos = new ArrayCollection();
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
    public function getFirstName(): ?string
    {
        return explode(separator: '.', string: $this->name)[0] ?? null;
    }

    public function setFirstName(string $firstName): self
    {
        $names = explode(separator: '.', string: $this->name);
        $lastName = $names[1] ?? '';
        $name = $firstName . '.' . $lastName;
        $this->name = strtolower(string: $name);
        return $this;
    }

    public function getLastName(): ?string
    {
        $names = explode(separator: '.', string: $this->name);
        return $names[1] ?? null;
    }

    public function setLastName(string $lastName): self
    {
        $names = explode(separator: '.', string: $this->name);
        $firstName = $names[0] ?? '';
        $name = $firstName . '.' . $lastName;
        $this->name = strtolower(string: $name);
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getTrainer(): ?Trainer
    {
        return $this->trainer;
    }

    public function setTrainer(?Trainer $trainer): static
    {
        // unset the owning side of the relation if necessary
        if ($trainer === null && $this->trainer !== null) {
            $this->trainer->setOperator(null);
        }

        // set the owning side of the relation if necessary
        if ($trainer !== null && $trainer->getOperator() !== $this) {
            $trainer->setOperator($this);
        }

        $this->trainer = $trainer;

        return $this;
    }

    public function isIsTrainer(): ?bool
    {
        return $this->isTrainer;
    }

    public function setIsTrainer(?bool $isTrainer): static
    {
        $this->isTrainer = $isTrainer;

        return $this;
    }

    public function getLasttraining(): ?\DateTimeInterface
    {
        return $this->lasttraining;
    }

    public function setLasttraining(?\DateTimeInterface $lasttraining): static
    {
        $this->lasttraining = $lasttraining;

        return $this;
    }

    public function getTobedeleted(): ?\DateTimeInterface
    {
        return $this->tobedeleted;
    }

    public function setTobedeleted(?\DateTimeInterface $tobedeleted): static
    {
        $this->tobedeleted = $tobedeleted;

        return $this;
    }

    public function getInactiveSince(): ?\DateTimeInterface
    {
        return $this->inactiveSince;
    }

    public function setInactiveSince(?\DateTimeInterface $inactiveSince): static
    {
        $this->inactiveSince = $inactiveSince;

        return $this;
    }

    /**
     * @return Collection<int, Uap>
     */
    public function getUaps(): Collection
    {
        return $this->uaps;
    }

    public function addUap(Uap $uap): static
    {
        if (!$this->uaps->contains($uap)) {
            $this->uaps->add($uap);
            $uap->addOperator($this);
        }

        return $this;
    }

    public function removeUap(Uap $uap): static
    {
        if ($this->uaps->removeElement($uap)) {
            $uap->removeOperator($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Iluo>
     */
    public function getIluos(): Collection
    {
        return $this->iluos;
    }

    public function addIluo(Iluo $iluo): static
    {
        if (!$this->iluos->contains($iluo)) {
            $this->iluos->add($iluo);
            $iluo->setOperator($this);
        }

        return $this;
    }

    public function removeIluo(Iluo $iluo): static
    {
        if ($this->iluos->removeElement($iluo)) {
            if ($iluo->getOperator() === $this) {
                $iluo->setOperator(null);
            }
        }

        return $this;
    }

    public function getEmploymentType(): ?EmploymentType
    {
        return $this->employmentType;
    }

    public function setEmploymentType(?EmploymentType $employmentType): static
    {
        $this->employmentType = $employmentType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setOperator(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getOperator() !== $this) {
            $user->setOperator($this);
        }

        $this->user = $user;

        return $this;
    }

    public function getShiftLeaders(): ?ShiftLeaders
    {
        return $this->shiftLeaders;
    }

    public function setShiftLeaders(?ShiftLeaders $shiftLeaders): static
    {
        // unset the owning side of the relation if necessary
        if ($shiftLeaders === null && $this->shiftLeaders !== null) {
            $this->shiftLeaders->setOperator(null);
        }

        // set the owning side of the relation if necessary
        if ($shiftLeaders !== null && $shiftLeaders->getOperator() !== $this) {
            $shiftLeaders->setOperator($this);
        }

        $this->shiftLeaders = $shiftLeaders;

        return $this;
    }
}
