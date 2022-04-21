<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
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
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="project")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Stage::class, mappedBy="project")
     */
    private $stage;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $profilePicture;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Cost::class, mappedBy="project")
     */
    private $costs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $project_directory;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="project_id", orphanRemoval=true)
     */
    private $documents;

    /**
     * @ORM\Column(type="float")
     */
    private $costEuro;


    /**
     * @ORM\Column(type="float")
     */
    private $costUsd;

    /**
     * @ORM\Column(type="float")
     */
    private $costRon;

    public function __construct()
    {
        $this->stage = new ArrayCollection();
        $this->costs = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Stage[]
     */
    public function getStage(): Collection
    {
        return $this->stage;
    }

    public function addStage(Stage $stage): self
    {
        if (!$this->stage->contains($stage)) {
            $this->stage[] = $stage;
            $stage->setProject($this);
        }

        return $this;
    }

    public function removeStage(Stage $stage): self
    {
        if ($this->stage->removeElement($stage)) {
            // set the owning side to null (unless already changed)
            if ($stage->getProject() === $this) {
                $stage->setProject(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
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

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|Cost[]
     */
    public function getCosts(): Collection
    {
        return $this->costs;
    }

    public function addCost(Cost $cost): self
    {
        if (!$this->costs->contains($cost)) {
            $this->costs[] = $cost;
            $cost->setProject($this);
        }

        return $this;
    }

    public function removeCost(Cost $cost): self
    {
        if ($this->costs->removeElement($cost)) {
            // set the owning side to null (unless already changed)
            if ($cost->getProject() === $this) {
                $cost->setProject(null);
            }
        }

        return $this;
    }

    public function getProjectDirectory(): ?string
    {
        return $this->project_directory;
    }

    public function setProjectDirectory(?string $project_directory): self
    {
        $this->project_directory = $project_directory;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setProjectId($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getProjectId() === $this) {
                $document->setProjectId(null);
            }
        }

        return $this;
    }

    public function getCostEuro(): ?float
    {
        return $this->costEuro;
    }

    public function setCostEuro(float $costEuro): self
    {
        $this->costEuro = $costEuro;

        return $this;
    }

    public function getSetCostUsd(): ?float
    {
        return $this->setCostUsd;
    }

    public function setSetCostUsd(float $setCostUsd): self
    {
        $this->setCostUsd = $setCostUsd;

        return $this;
    }

    public function getCostUsd(): ?float
    {
        return $this->costUsd;
    }

    public function setCostUsd(float $costUsd): self
    {
        $this->costUsd = $costUsd;

        return $this;
    }

    public function getCostRon(): ?float
    {
        return $this->costRon;
    }

    public function setCostRon(float $costRon): self
    {
        $this->costRon = $costRon;

        return $this;
    }
    public function addCostsToProject(float $costEuro, float $costUsd, float $costRon){

        $currentCostEuro = $this->getCostEuro();
        $currentCostUsd = $this->getCostUsd();
        $currentCostRon = $this->getCostRon();

        $newCostEuro = $currentCostEuro + $costEuro;
        $newCostUsd = $currentCostUsd + $costUsd;
        $newCostRon = $currentCostRon + $costRon;

        self::setCostEuro($newCostEuro);
        self::setCostUsd($newCostUsd);
        self::setCostRon($newCostRon);
    }
}
