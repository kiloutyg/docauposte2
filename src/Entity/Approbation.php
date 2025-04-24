<?php

namespace App\Entity;

use App\Repository\ApprobationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApprobationRepository::class)]
class Approbation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'approbations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Validation $Validation = null;

    #[ORM\ManyToOne(inversedBy: 'approbations')]
    private ?User $UserApprobator = null;

    #[ORM\Column(nullable: true)]
    private ?bool $approval = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Comment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $Approved_at = null;

    #[ORM\ManyToOne(inversedBy: 'approbations')]
    private ?Department $DepartmentApprobator = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValidation(): ?Validation
    {
        return $this->Validation;
    }

    public function setValidation(?Validation $Validation): static
    {
        $this->Validation = $Validation;

        return $this;
    }

    public function getUserApprobator(): ?User
    {
        return $this->UserApprobator;
    }

    public function setUserApprobator(?User $UserApprobator): static
    {
        $this->UserApprobator = $UserApprobator;

        return $this;
    }

    public function isApproval(): ?bool
    {
        return $this->approval;
    }

    public function setApproval(?bool $approval): static
    {
        $this->approval = $approval;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->Comment;
    }

    public function setComment(?string $Comment): static
    {
        $this->Comment = $Comment;

        return $this;
    }

    public function getApprovedAt(): ?\DateTimeInterface
    {
        return $this->Approved_at;
    }

    public function setApprovedAt(?\DateTimeInterface $Approved_at): static
    {
        $this->Approved_at = $Approved_at;

        return $this;
    }

    public function getDepartmentApprobator(): ?Department
    {
        return $this->DepartmentApprobator;
    }

    public function setDepartmentApprobator(?Department $DepartmentApprobator): static
    {
        $this->DepartmentApprobator = $DepartmentApprobator;

        return $this;
    }
}