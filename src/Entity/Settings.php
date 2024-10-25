<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $ValidatorNumber = null;

    #[ORM\Column(nullable: true)]
    private ?bool $Training = null;

    #[ORM\Column(nullable: true)]
    private ?bool $AutoDisplayIncident = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $AutoDisplayIncidentTimer = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $AutoDeleteOperatorDelay = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValidatorNumber(): ?int
    {
        return $this->ValidatorNumber;
    }

    public function setValidatorNumber(?int $ValidatorNumber): static
    {
        $this->ValidatorNumber = $ValidatorNumber;

        return $this;
    }

    public function isTraining(): ?bool
    {
        return $this->Training;
    }

    public function setTraining(?bool $Training): static
    {
        $this->Training = $Training;

        return $this;
    }

    public function isAutoDisplayIncident(): ?bool
    {
        return $this->AutoDisplayIncident;
    }

    public function setAutoDisplayIncident(?bool $AutoDisplayIncident): static
    {
        $this->AutoDisplayIncident = $AutoDisplayIncident;

        return $this;
    }

    public function getAutoDisplayIncidentTimer(): ?\DateTimeInterface
    {
        return $this->AutoDisplayIncidentTimer;
    }

    public function setAutoDisplayIncidentTimer(?\DateTimeInterface $AutoDisplayIncidentTimer): static
    {
        $this->AutoDisplayIncidentTimer = $AutoDisplayIncidentTimer;

        return $this;
    }

    public function getAutoDeleteOperatorDelay(): ?\DateTimeInterface
    {
        return $this->AutoDeleteOperatorDelay;
    }

    public function setAutoDeleteOperatorDelay(?\DateTimeInterface $AutoDeleteOperatorDelay): static
    {
        $this->AutoDeleteOperatorDelay = $AutoDeleteOperatorDelay;

        return $this;
    }
}
