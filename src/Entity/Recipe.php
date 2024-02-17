<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $ingredients = null;

    #[ORM\Column]
    private ?int $created_by = null;

    #[ORM\Column(length: 255)]
    private ?string $instructions = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'recipe_id')]
    private ?FavoriteRecipe $favoriteRecipe = null;

    #[ORM\ManyToOne(inversedBy: 'recipe_id')]
    private ?RecipeCollection $recipeCollection = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }

    public function setIngredients(string $ingredients): self
    {
        $this->ingredients = $ingredients;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->created_by;
    }

    public function setCreatedBy(int $created_by): self
    {
        $this->created_by = $created_by;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getFavoriteRecipe(): ?FavoriteRecipe
    {
        return $this->favoriteRecipe;
    }

    public function setFavoriteRecipe(?FavoriteRecipe $favoriteRecipe): self
    {
        $this->favoriteRecipe = $favoriteRecipe;

        return $this;
    }

    public function getRecipeCollection(): ?RecipeCollection
    {
        return $this->recipeCollection;
    }

    public function setRecipeCollection(?RecipeCollection $recipeCollection): self
    {
        $this->recipeCollection = $recipeCollection;

        return $this;
    }
}
