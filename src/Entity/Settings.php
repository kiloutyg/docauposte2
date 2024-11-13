<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private ?int $id = 1;
    
    #[ORM\Column(nullable: true)]
    private ?bool $UploadValidation = null;

    #[ORM\Column(nullable: true)]
    private ?int $ValidatorNumber = null;

    #[ORM\Column(nullable: true)]
    private ?bool $AutoDisplayIncident = null;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true)]
    private ?\DateInterval $AutoDisplayIncidentTimer = null;

    #[ORM\Column(nullable: true)]
    private ?bool $Training = null;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true)]
    private ?\DateInterval $OperatorRetrainingDelay = null;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true)]
    private ?\DateInterval $AutoDeleteOperatorDelay = null;

    #[ORM\Column(nullable: true)]
    private ?\DateInterval $OperatorInactivityDelay = null;

    public function __construct()
    {
        $this->id = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isUploadValidation(): ?bool
    {
        return $this->UploadValidation;
    }

    public function setUploadValidation(?bool $UploadValidation): static
    {
        $this->UploadValidation = $UploadValidation;

        return $this;
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


    public function isAutoDisplayIncident(): ?bool
    {
        return $this->AutoDisplayIncident;
    }

    public function setAutoDisplayIncident(?bool $AutoDisplayIncident): static
    {
        $this->AutoDisplayIncident = $AutoDisplayIncident;

        return $this;
    }

    public function getAutoDisplayIncidentTimer(): ?\DateInterval
    {
        return $this->AutoDisplayIncidentTimer;
    }

    public function setAutoDisplayIncidentTimer(?\DateInterval $AutoDisplayIncidentTimer): static
    {
        $this->AutoDisplayIncidentTimer = $AutoDisplayIncidentTimer;

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

    public function getOperatorRetrainingDelay(): ?\DateInterval
    {
        return $this->OperatorRetrainingDelay;
    }

    public function setOperatorRetrainingDelay(?\DateInterval $OperatorRetrainingDelay): static
    {
        $this->OperatorRetrainingDelay = $OperatorRetrainingDelay;

        return $this;
    }

    public function getAutoDeleteOperatorDelay(): ?\DateInterval
    {
        return $this->AutoDeleteOperatorDelay;
    }

    public function setAutoDeleteOperatorDelay(?\DateInterval $AutoDeleteOperatorDelay): static
    {
        $this->AutoDeleteOperatorDelay = $AutoDeleteOperatorDelay;

        return $this;
    }

    public function getOperatorInactivityDelay(): ?\DateInterval
    {
        return $this->OperatorInactivityDelay;
    }

    public function setOperatorInactivityDelay(?\DateInterval $OperatorInactivityDelay): static
    {
        $this->OperatorInactivityDelay = $OperatorInactivityDelay;

        return $this;
    }

}
