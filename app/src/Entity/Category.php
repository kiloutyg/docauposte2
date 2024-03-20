<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductLine $ProductLine = null;

    #[ORM\OneToMany(mappedBy: 'Category', targetEntity: Button::class)]
    private Collection $buttons;

    #[ORM\Column(nullable: true)]
    private ?int $SortOrder = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    private ?User $Creator = null;



    public function __construct()
    {
        $this->buttons = new ArrayCollection();
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

    public function getProductLine(): ?ProductLine
    {
        return $this->ProductLine;
    }

    public function setProductLine(?ProductLine $ProductLine): self
    {
        $this->ProductLine = $ProductLine;

        return $this;
    }

    /**
     * @return Collection<int, Button>
     */
    public function getButtons(): Collection
    {
        return $this->buttons;
    }

    public function addButton(Button $button): self
    {
        if (!$this->buttons->contains($button)) {
            $this->buttons->add($button);
            $button->setCategory($this);
        }

        return $this;
    }

    public function removeButton(Button $button): self
    {
        if ($this->buttons->removeElement($button)) {
            // set the owning side to null (unless already changed)
            if ($button->getCategory() === $this) {
                $button->setCategory(null);
            }
        }

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->SortOrder;
    }

    public function setSortOrder(?int $SortOrder): static
    {
        $this->SortOrder = $SortOrder;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->Creator;
    }

    public function setCreator(?User $Creator): static
    {
        $this->Creator = $Creator;

        return $this;
    }
}