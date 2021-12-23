<?php

namespace App\Entity;

use App\Repository\CostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CostRepository::class)
 */
class Cost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     */
    private $value;
    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="costs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $eur;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $usd;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ron;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }
    public function getEur(): ?float
    {
        return $this->eur;
    }

    public function setEur(?float $eur): self
    {
       $this->eur = $eur;

        return $this;
    }
    public function getUsd(): ?float
    {
        return $this->usd;
    }

    public function setUsd(?float $usd): self
    {
        $this->usd = $usd;

        return $this;
    }

    public function getRon(): ?float
    {
        return $this->ron;
    }

    public function setRon(?float $ron): self
    {
        $this->ron = $ron;

        return $this;
    }
}
