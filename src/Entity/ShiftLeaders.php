<?php

namespace App\Entity;

use App\Repository\ShiftLeadersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShiftLeadersRepository::class)]
class ShiftLeaders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Iluo>
     */
    #[ORM\OneToMany(targetEntity: Iluo::class, mappedBy: 'shiftLeader')]
    private Collection $iluos;

    /**
     * @var Collection<int, IluoChecklist>
     */
    #[ORM\OneToMany(targetEntity: IluoChecklist::class, mappedBy: 'shiftLeader')]
    private Collection $iluoChecklists;

    public function __construct()
    {
        $this->iluos = new ArrayCollection();
        $this->iluoChecklists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            $iluo->setShiftLeader($this);
        }

        return $this;
    }

    public function removeIluo(Iluo $iluo): static
    {
        if ($this->iluos->removeElement($iluo)) {
            // set the owning side to null (unless already changed)
            if ($iluo->getShiftLeader() === $this) {
                $iluo->setShiftLeader(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IluoChecklist>
     */
    public function getIluoChecklists(): Collection
    {
        return $this->iluoChecklists;
    }

    public function addIluoChecklist(IluoChecklist $iluoChecklist): static
    {
        if (!$this->iluoChecklists->contains($iluoChecklist)) {
            $this->iluoChecklists->add($iluoChecklist);
            $iluoChecklist->setShiftLeader($this);
        }

        return $this;
    }

    public function removeIluoChecklist(IluoChecklist $iluoChecklist): static
    {
        if ($this->iluoChecklists->removeElement($iluoChecklist)) {
            // set the owning side to null (unless already changed)
            if ($iluoChecklist->getShiftLeader() === $this) {
                $iluoChecklist->setShiftLeader(null);
            }
        }

        return $this;
    }
}
