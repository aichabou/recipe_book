<?php

namespace App\Entity;

use App\Repository\RecipeCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeCollectionRepository::class)]
class RecipeCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?user $user_id = null;

    #[ORM\OneToMany(mappedBy: 'recipeCollection', targetEntity: Recipe::class)]
    private Collection $recipe_id;

    public function __construct()
    {
        $this->recipe_id = new ArrayCollection();
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

    public function getUserId(): ?user
    {
        return $this->user_id;
    }

    public function setUserId(?user $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipeId(): Collection
    {
        return $this->recipe_id;
    }

    public function addRecipeId(Recipe $recipeId): self
    {
        if (!$this->recipe_id->contains($recipeId)) {
            $this->recipe_id->add($recipeId);
            $recipeId->setRecipeCollection($this);
        }

        return $this;
    }

    public function removeRecipeId(Recipe $recipeId): self
    {
        if ($this->recipe_id->removeElement($recipeId)) {
            // set the owning side to null (unless already changed)
            if ($recipeId->getRecipeCollection() === $this) {
                $recipeId->setRecipeCollection(null);
            }
        }

        return $this;
    }
}
