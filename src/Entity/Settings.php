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

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $uploadValidation = null;

    #[ORM\Column(nullable: true, options: ['default' => 4])]
    private ?int $validatorNumber = null;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $incidentAutoDisplay = null;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true, options: ['default' => 'P00Y00M00DT00H10M00S'])]
    private ?\DateInterval $incidentAutoDisplayTimer = null;

    #[ORM\Column(nullable: true)]
    private ?bool $training = true;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true, options: ['default' => 'P00Y06M00DT00H00M00S'])]
    private ?\DateInterval $operatorRetrainingDelay = null;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true, options: ['default' => 'P00Y03M00DT00H00M00S'])]
    private ?\DateInterval $operatorInactivityDelay = null;

    #[ORM\Column(type: Types::DATEINTERVAL, nullable: true, options: ['default' => 'P00Y03M00DT00H00M00S'])]
    private ?\DateInterval $operatorAutoDeleteDelay = null;

    #[ORM\Column(nullable: false, options: ['default' => true])]
    private ?bool $operatorCodeMethod = null;

    #[ORM\Column(length: 255, nullable: true, options: ['default' => '[0-9]{5}'])]
    private ?string $operatorCodeRegex = null;

    public function __construct()
    {
        $this->id = 1;
        $this->uploadValidation = true;
        $this->validatorNumber = 4;
        $this->incidentAutoDisplay = true;
        $this->incidentAutoDisplayTimer = new \DateInterval('P00Y00M00DT00H10M00S');
        $this->training = true;
        $this->operatorRetrainingDelay = new \DateInterval('P00Y06M00DT00H00M00S');
        $this->operatorInactivityDelay = new \DateInterval('P00Y03M00DT00H00M00S');
        $this->operatorAutoDeleteDelay = new \DateInterval('P00Y03M00DT00H00M00S');
        $this->operatorCodeMethod = true;
        $this->operatorCodeRegex = '[0-9]{5}';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isUploadValidation(): ?bool
    {
        return $this->uploadValidation;
    }

    public function setUploadValidation(?bool $uploadValidation): static
    {
        $this->uploadValidation = $uploadValidation;

        return $this;
    }

    public function getValidatorNumber(): ?int
    {
        return $this->validatorNumber;
    }

    public function setValidatorNumber(?int $validatorNumber): static
    {
        $this->validatorNumber = $validatorNumber;

        return $this;
    }


    public function isIncidentAutoDisplay(): ?bool
    {
        return $this->incidentAutoDisplay;
    }

    public function setIncidentAutoDisplay(?bool $incidentAutoDisplay): static
    {
        $this->incidentAutoDisplay = $incidentAutoDisplay;

        return $this;
    }

    public function getIncidentAutoDisplayTimer(): ?\DateInterval
    {
        return $this->incidentAutoDisplayTimer;
    }

    public function setIncidentAutoDisplayTimer(?\DateInterval $incidentAutoDisplayTimer): static
    {
        $this->incidentAutoDisplayTimer = $incidentAutoDisplayTimer;

        return $this;
    }

    public function isTraining(): ?bool
    {
        return $this->training;
    }

    public function setTraining(?bool $training): static
    {
        $this->training = $training;

        return $this;
    }

    public function getOperatorRetrainingDelay(): ?\DateInterval
    {
        return $this->operatorRetrainingDelay;
    }

    public function setOperatorRetrainingDelay(?\DateInterval $operatorRetrainingDelay): static
    {
        $this->operatorRetrainingDelay = $operatorRetrainingDelay;

        return $this;
    }

    public function getOperatorInactivityDelay(): ?\DateInterval
    {
        return $this->operatorInactivityDelay;
    }

    public function setOperatorInactivityDelay(?\DateInterval $operatorInactivityDelay): static
    {
        $this->operatorInactivityDelay = $operatorInactivityDelay;

        return $this;
    }

    public function getOperatorAutoDeleteDelay(): ?\DateInterval
    {
        return $this->operatorAutoDeleteDelay;
    }

    public function setOperatorAutoDeleteDelay(?\DateInterval $operatorAutoDeleteDelay): static
    {
        $this->operatorAutoDeleteDelay = $operatorAutoDeleteDelay;

        return $this;
    }

    public function isOperatorCodeMethod(): ?bool
    {
        return $this->operatorCodeMethod;
    }

    public function setOperatorCodeMethod(bool $operatorCodeMethod): static
    {
        $this->operatorCodeMethod = $operatorCodeMethod;

        return $this;
    }

    public function getOperatorCodeRegex(): ?string
    {
        return $this->operatorCodeRegex;
    }

    public function setOperatorCodeRegex(?string $operatorCodeRegex): static
    {
        $this->operatorCodeRegex = $operatorCodeRegex;

        return $this;
    }
}
