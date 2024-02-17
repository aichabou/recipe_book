<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @param RecipeRepository $recipeRepository
     * @return Response
     */
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        $recipe = $recipeRepository->findAll();

        return $this->render('home/index.html.twig', [
            'recipes' => $recipe,
        ]);
    }
}
