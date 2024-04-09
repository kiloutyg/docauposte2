<?php

namespace App\Entity;

use App\Repository\TrainingRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: TrainingRecordRepository::class)]
#[Broadcast]
class TrainingRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trainingRecords')]
    private ?Upload $Upload = null;

    #[ORM\ManyToOne(inversedBy: 'trainingRecords')]
    private ?Operator $operator = null;

    #[ORM\Column]
    private ?bool $trained = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $iluo = null;

    #[ORM\ManyToOne(inversedBy: 'trainingRecords')]
    private ?Trainer $trainer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpload(): ?Upload
    {
        return $this->Upload;
    }

    public function setUpload(?Upload $Upload): static
    {
        $this->Upload = $Upload;

        return $this;
    }

    public function getOperator(): ?Operator
    {
        return $this->operator;
    }

    public function setOperator(?Operator $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function isTrained(): ?bool
    {
        return $this->trained;
    }

    public function setTrained(bool $trained): static
    {
        $this->trained = $trained;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getIluo(): ?string
    {
        return $this->iluo;
    }

    public function setIluo(?string $iluo): static
    {
        $this->iluo = $iluo;

        return $this;
    }

    public function getTrainer(): ?Trainer
    {
        return $this->trainer;
    }

    public function setTrainer(?Trainer $trainer): static
    {
        $this->trainer = $trainer;

        return $this;
    }
}
