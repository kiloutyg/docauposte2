<?php

namespace App\Entity;

use App\Repository\ValidationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValidationRepository::class)]
class Validation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'validation', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Upload $Upload = null;

    #[ORM\ManyToMany(targetEntity: Department::class, inversedBy: 'validations')]
    private Collection $department;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'validations')]
    private Collection $validator;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function __construct()
    {
        $this->department = new ArrayCollection();
        $this->validator = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpload(): ?Upload
    {
        return $this->Upload;
    }

    public function setUpload(Upload $Upload): static
    {
        $this->Upload = $Upload;

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartment(): Collection
    {
        return $this->department;
    }

    public function addDepartment(Department $department): static
    {
        if (!$this->department->contains($department)) {
            $this->department->add($department);
        }

        return $this;
    }

    public function removeDepartment(Department $department): static
    {
        $this->department->removeElement($department);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getValidator(): Collection
    {
        return $this->validator;
    }

    public function addValidator(User $validator): static
    {
        if (!$this->validator->contains($validator)) {
            $this->validator->add($validator);
        }

        return $this;
    }

    public function removeValidator(User $validator): static
    {
        $this->validator->removeElement($validator);

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
