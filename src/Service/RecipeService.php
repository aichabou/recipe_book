<?php

namespace App\Service;

use App\Entity\Recipe;
use App\Entity\User;
use App\Entity\FavoriteRecipe;
use Doctrine\ORM\EntityManagerInterface;

class RecipeService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function getRecipeById($id)
    {
        return $this->entityManager->getRepository(Recipe::class)->find($id);
    }

    public function addRecipeToFavorites(User $user, Recipe $recipe)
    {
        $favoriteRecipe = $this->entityManager->getRepository(FavoriteRecipe::class)->findOneBy([
            'user_id' => $user,
            'id' => $recipe,
        ]);

        if (!$favoriteRecipe) {
            $favoriteRecipe = new FavoriteRecipe();
            $favoriteRecipe->setUserId($user);
            $favoriteRecipe->addRecipeId($recipe);
            $this->entityManager->persist($favoriteRecipe);
            $this->entityManager->flush();
        }
    }
}
