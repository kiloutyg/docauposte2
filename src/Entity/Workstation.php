<?php

namespace App\Entity;

use App\Repository\WorkstationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: WorkstationRepository::class)]
#[UniqueEntity(fields: 'name', message: 'Un poste de travail avec ce nom existe déjà.')]
class Workstation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9\s\-\/\(\)\+\.]+$/',
        message: 'Le nom de poste de travail doit être au format : Assy-P674-Poinçonneuse Peau'
    )]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'workstations')]
    private ?Upload $upload = null;

    #[ORM\ManyToOne(inversedBy: 'workstations')]
    private ?Products $products = null;

    #[ORM\ManyToOne(inversedBy: 'workstations')]
    private ?Department $department = null;

    #[ORM\ManyToOne(inversedBy: 'workstations')]
    private ?Zone $zone = null;

    /**
     * @var Collection<int, Iluo>
     */
    #[ORM\OneToMany(targetEntity: Iluo::class, mappedBy: 'workstation')]
    private Collection $iluos;

    #[ORM\ManyToOne(inversedBy: 'workstations')]
    private ?Uap $uap = null;

    public function __construct()
    {
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

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUpload(): ?Upload
    {
        return $this->upload;
    }

    public function setUpload(?Upload $upload): static
    {
        $this->upload = $upload;

        return $this;
    }

    public function getProducts(): ?Products
    {
        return $this->products;
    }

    public function setProducts(?Products $products): static
    {
        $this->products = $products;

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

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;

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
            $iluo->setWorkstation($this);
        }

        return $this;
    }

    public function removeIluo(Iluo $iluo): static
    {
        if ($this->iluos->removeElement($iluo)) {
            // set the owning side to null (unless already changed)
            if ($iluo->getWorkstation() === $this) {
                $iluo->setWorkstation(null);
            }
        }

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
