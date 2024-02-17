<?php

namespace App\Entity;

use App\Repository\FavoriteRecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRecipeRepository::class)]
class FavoriteRecipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'favoriteRecipe', targetEntity: Recipe::class)]
    private Collection $recipe_id;

    #[ORM\ManyToOne(inversedBy: 'favoriteRecipes')]
    private ?User $user_id = null;

    public function __construct()
    {
        $this->recipe_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function __toString()
    {
        return (string) $this->id;
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
            $recipeId->setFavoriteRecipe($this);
        }

        return $this;
    }

    public function removeRecipeId(Recipe $recipeId): self
    {
        if ($this->recipe_id->removeElement($recipeId)) {
            // set the owning side to null (unless already changed)
            if ($recipeId->getFavoriteRecipe() === $this) {
                $recipeId->setFavoriteRecipe(null);
            }
        }

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }
}
