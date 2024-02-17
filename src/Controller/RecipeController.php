<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\FavoriteRecipe;
use App\Entity\RecipeCollection;
use App\Form\RecipeCollectionType;
use App\Form\RecipeType;
use App\Service\RecipeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    private $recipeService;
    private EntityManagerInterface $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/recipe/add', name: 'app_recipe')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recipe = new Recipe();

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($recipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_home', ['id' => $recipe->getId()]);
        }

        return $this->render('recipe/recipe.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Recipe $recipe
     * @return Response
     */
    #[Route('/recipe/find/{name}', name: 'app_recipe_id')]
    public function show(EntityManagerInterface $entityManager, string $name): Response
    {
        $query = $entityManager->createQuery(
            'SELECT r
            FROM App\Entity\Recipe r
            WHERE r.name = :name'
        )->setParameter('name', $name);

        $recipe = $query->getOneOrNullResult();

        return $this->render('recipe/recipeId.html.twig', [
            'recipe' => $recipe
        ]);
    }

    /**
     * @param Recipe $recipe
     * @param Request $request
     * @return Response
     */
    #[Route('/recipe/update/{recipe}', name: 'app_recipe_update')]
    public function update(Recipe $recipe, Request $request): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($recipe);
            $this->em->flush();
            return $this->redirectToRoute('app_recipe');
        }
        return $this->render('recipe/recipe.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Recipe $recipe
     * @param Request $request
     * @return Response
     */
    #[Route('/recipe/delete/{recipe}', name: 'app_recipe_delete')]
    public function delete(Recipe $recipe, Request $request): Response
    {
        $this->em->remove($recipe);
        $this->em->flush();

        return $this->redirectToRoute("app_recipe");
    }

    /**
     * @param Recipe $recipe
     * @param Request $request
     * @return Response
     */
    #[Route('/recipe/favorite/{recipe}', name: 'app_recipe_favorite')]
    public function favoriteRecipe(Recipe $recipe, Request $request, RecipeService $recipeService, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $repository = $entityManager->getRepository(FavoriteRecipe::class);
        $favoriteRecipe = $repository->findOneBy(['user_id' => $user, 'id' => $recipe]);

        if (!$favoriteRecipe) {
            $recipeService->addRecipeToFavorites($user, $recipe);
            $this->addFlash('success', 'La recette est bien ajouté à favorite.');
        } else {
            $this->addFlash('warning', 'La recette est dèja dans favorite.');
        }

        return $this->redirectToRoute('app_recipe_id', [
            'id' => $recipe->getId(),
            'name' => $recipe->getName(),
            'message' => 'recipe_favorite'
        ]);
    }

    /**
     * @param Recipe $recipe
     * @param Request $request
     * @return Response
     */
    #[Route('/recipe/collection/{recipe}', name: 'app_recipe_collection')]
    public function collection(Recipe $recipe, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération de la collection associée à l'utilisateur courant et à la recette en question
        $repository = $entityManager->getRepository(RecipeCollection::class);
        $recipeCollection = $repository->findOneBy([
            'user_id' => $this->getUser(),
            'id' => $recipe
        ]);

        // Création d'un nouveau formulaire pour ajouter la recette à une collection
        $form = $this->createForm(RecipeCollectionType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $data = $form->getData();

            // Création d'une nouvelle collection si elle n'existe pas déjà
            if (!$recipeCollection) {
                $recipeCollection = new RecipeCollection();
                $recipeCollection->setName($data['name']);
                $recipeCollection->setUserId($this->getUser());
            }

            // Ajout de la recette à la collection
            $recipeCollection->addRecipeId($recipe);
            $entityManager->persist($recipeCollection);
            $entityManager->flush();

            $this->addFlash('success', 'Recipe added to collection.');

            return $this->redirectToRoute('app_recipe_collection', [
                'recipe' => $recipe->getId(),
            ]);
        }

        return $this->render('recipe/collection.html.twig', [
            'form' => $form->createView(),
            'recipe' => $recipe,
            'recipeCollection' => $recipeCollection,
        ]);
    }
}
