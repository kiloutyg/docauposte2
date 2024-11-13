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
    private ?bool $UploadValidation = true;

    #[ORM\Column(nullable: true)]
    private ?int $ValidatorNumber = 4;

    #[ORM\Column(nullable: true)]
    private ?bool $IncidentAutoDisplay = true;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true)]
    private ?\DateInterval $IncidentAutoDisplayTimer = null;

    #[ORM\Column(nullable: true)]
    private ?bool $Training = true;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true)]
    private ?\DateInterval $OperatorRetrainingDelay = null;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true)]
    private ?\DateInterval $OperatorInactivityDelay = null;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true)]
    private ?\DateInterval $OperatorAutoDeleteDelay = null;

    public function __construct()
    {
        $this->id = 1;
        $this->UploadValidation = true;
        $this->ValidatorNumber = 4;
        $this->IncidentAutoDisplay = true;
        $this->IncidentAutoDisplayTimer = new \DateInterval('P0Y0M0DT0H10M0S');
        $this->Training = true;
        $this->OperatorRetrainingDelay = new \DateInterval('P0Y6M0DT0H0M0S');
        $this->OperatorInactivityDelay = new \DateInterval('P0Y3M0DT0H0M0S');
        $this->OperatorAutoDeleteDelay = new \DateInterval('P0Y3M0DT0H0M0S');
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


    public function isIncidentAutoDisplay(): ?bool
    {
        return $this->IncidentAutoDisplay;
    }

    public function setIncidentAutoDisplay(?bool $IncidentAutoDisplay): static
    {
        $this->IncidentAutoDisplay = $IncidentAutoDisplay;

        return $this;
    }

    public function getIncidentAutoDisplayTimer(): ?\DateInterval
    {
        return $this->IncidentAutoDisplayTimer;
    }

    public function setIncidentAutoDisplayTimer(?\DateInterval $IncidentAutoDisplayTimer): static
    {
        $this->IncidentAutoDisplayTimer = $IncidentAutoDisplayTimer;

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

    public function getOperatorInactivityDelay(): ?\DateInterval
    {
        return $this->OperatorInactivityDelay;
    }

    public function setOperatorInactivityDelay(?\DateInterval $OperatorInactivityDelay): static
    {
        $this->OperatorInactivityDelay = $OperatorInactivityDelay;

        return $this;
    }

    public function getOperatorAutoDeleteDelay(): ?\DateInterval
    {
        return $this->OperatorAutoDeleteDelay;
    }

    public function setOperatorAutoDeleteDelay(?\DateInterval $OperatorAutoDeleteDelay): static
    {
        $this->OperatorAutoDeleteDelay = $OperatorAutoDeleteDelay;

        return $this;
    }
}
