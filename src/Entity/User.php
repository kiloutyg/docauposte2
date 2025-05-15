<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: 'username', message: 'Un Utilisateur avec ce nom existe déjà.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex(
        pattern: '/^(it-[a-z]+|[a-z]+(?:-[a-z]+)?\.[a-z]+(?:-[a-z]+)?)$/',
        message: 'Le nom d\'utilisateur doit être au format prénom.nom, prénom-nom.nom, ou it-polangres'
    )]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Department $department = null;

    #[ORM\Column(length: 255, nullable: false, unique: true)]
    #[Assert\Email(message: 'L\'adresse email n\'est pas valide.')]
    private ?string $emailAddress = null;

    #[ORM\OneToMany(mappedBy: 'uploader', targetEntity: Upload::class)]
    private Collection $uploads;

    #[ORM\OneToMany(mappedBy: 'olduploader', targetEntity: OldUpload::class)]
    private Collection $olduploads;

    #[ORM\OneToMany(mappedBy: 'Uploader', targetEntity: Incident::class)]
    private Collection $incidents;

    #[ORM\OneToMany(mappedBy: 'UserApprobator', targetEntity: Approbation::class)]
    private Collection $approbations;

    #[ORM\Column(nullable: true)]
    private ?bool $blocked = null;

    #[ORM\OneToMany(mappedBy: 'Creator', targetEntity: Zone::class)]
    private Collection $zones;

    #[ORM\OneToMany(mappedBy: 'Creator', targetEntity: ProductLine::class)]
    private Collection $productLines;

    #[ORM\OneToMany(mappedBy: 'Creator', targetEntity: Category::class)]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'Creator', targetEntity: Button::class)]
    private Collection $buttons;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: ShiftLeaders::class, cascade: ['remove'])]
    private ?ShiftLeaders $shiftLeader = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: QualityRep::class, cascade: ['remove'])]
    private ?QualityRep $qualityRep = null;

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Operator $operator = null;

    public function __construct()
    {
        $this->uploads = new ArrayCollection();
        $this->olduploads = new ArrayCollection();
        $this->incidents = new ArrayCollection();
        $this->approbations = new ArrayCollection();
        $this->zones = new ArrayCollection();
        $this->productLines = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->buttons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
        // You can also add this line to help with profiler security
        $this->password = '[REDACTED]';
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * @return Collection<int, Upload>
     */
    public function getUploads(): Collection
    {
        return $this->uploads;
    }

    public function addUpload(Upload $upload): static
    {
        if (!$this->uploads->contains($upload)) {
            $this->uploads->add($upload);
            $upload->setUploader($this);
        }

        return $this;
    }

    public function removeUpload(Upload $upload): static
    {
        if ($this->uploads->removeElement($upload)) {
            // set the owning side to null (unless already changed)
            if ($upload->getUploader() === $this) {
                $upload->setUploader(null);
            }
        }

        return $this;
    }


    public function getUploadsInValidation(): Collection
    {
        return $this->uploads->filter(function (Upload $upload) {
            $validation = $upload->getValidation();
            // Check if validation is not null and then check if the status is null
            return $validation !== null && $validation->isStatus() === null;
        });
    }


    public function getUploadsInRefusedValidation(): Collection
    {
        return $this->uploads->filter(function (Upload $upload) {
            $validation = $upload->getValidation();
            // Check if validation is not null and then check if the status is null
            return $validation !== null && $validation->isStatus() === false;
        });
    }

    /**
     * @return Collection<int, Upload>
     */
    public function getOldUploads(): Collection
    {
        return $this->olduploads;
    }

    public function addOldUpload(OldUpload $oldupload): static
    {
        if (!$this->olduploads->contains($oldupload)) {
            $this->olduploads->add($oldupload);
            $oldupload->setOldUploader($this);
        }

        return $this;
    }

    public function removeOldUpload(OldUpload $oldupload): static
    {
        if ($this->olduploads->removeElement($oldupload)) {
            // set the owning side to null (unless already changed)
            if ($oldupload->getOldUploader() === $this) {
                $oldupload->setOldUploader(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Incident>
     */
    public function getIncidents(): Collection
    {
        return $this->incidents;
    }

    public function addIncident(Incident $incident): static
    {
        if (!$this->incidents->contains($incident)) {
            $this->incidents->add($incident);
            $incident->setUploader($this);
        }

        return $this;
    }

    public function removeIncident(Incident $incident): static
    {
        if ($this->incidents->removeElement($incident)) {
            // set the owning side to null (unless already changed)
            if ($incident->getUploader() === $this) {
                $incident->setUploader(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Approbation>
     */
    public function getApprobations(): Collection
    {
        return $this->approbations;
    }

    public function addApprobation(Approbation $approbation): static
    {
        if (!$this->approbations->contains($approbation)) {
            $this->approbations->add($approbation);
            $approbation->setUserApprobator($this);
        }

        return $this;
    }

    public function removeApprobation(Approbation $approbation): static
    {
        if ($this->approbations->removeElement($approbation)) {
            // set the owning side to null (unless already changed)
            if ($approbation->getUserApprobator() === $this) {
                $approbation->setUserApprobator(null);
            }
        }

        return $this;
    }

    public function isBlocked(): ?bool
    {
        return $this->blocked;
    }

    public function setBlocked(?bool $blocked): static
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * @return Collection<int, Zone>
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $zone): static
    {
        if (!$this->zones->contains($zone)) {
            $this->zones->add($zone);
            $zone->setCreator($this);
        }

        return $this;
    }

    public function removeZone(Zone $zone): static
    {
        if ($this->zones->removeElement($zone)) {
            // set the owning side to null (unless already changed)
            if ($zone->getCreator() === $this) {
                $zone->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductLine>
     */
    public function getProductLines(): Collection
    {
        return $this->productLines;
    }

    public function addProductLine(ProductLine $productLine): static
    {
        if (!$this->productLines->contains($productLine)) {
            $this->productLines->add($productLine);
            $productLine->setCreator($this);
        }

        return $this;
    }

    public function removeProductLine(ProductLine $productLine): static
    {
        if ($this->productLines->removeElement($productLine)) {
            // set the owning side to null (unless already changed)
            if ($productLine->getCreator() === $this) {
                $productLine->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setCreator($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCreator() === $this) {
                $category->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Button>
     */
    public function getButtons(): Collection
    {
        return $this->buttons;
    }

    public function addButton(Button $button): static
    {
        if (!$this->buttons->contains($button)) {
            $this->buttons->add($button);
            $button->setCreator($this);
        }

        return $this;
    }

    public function removeButton(Button $button): static
    {
        if ($this->buttons->removeElement($button)) {
            // set the owning side to null (unless already changed)
            if ($button->getCreator() === $this) {
                $button->setCreator(null);
            }
        }

        return $this;
    }

    public function getShiftLeader(): ?ShiftLeaders
    {
        return $this->shiftLeader;
    }

    public function setShiftLeader(?ShiftLeaders $shiftLeader): static
    {
        $this->shiftLeader = $shiftLeader;

        return $this;
    }

    public function getQualityRep(): ?QualityRep
    {
        return $this->qualityRep;
    }

    public function setQualityRep(?QualityRep $qualityRep): static
    {
        $this->qualityRep = $qualityRep;

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
}
