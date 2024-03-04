<?php

namespace App\Entity;

use App\Repository\TrainingRecordRepository;
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
}
