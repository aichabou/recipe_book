<?php

namespace App\Controller;

use Doctrine\DBAL\ParameterType;
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
    public function new(Request $request): Response
    {
        $recipe = new Recipe();

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conn = $this->em->getConnection();
            $sql = '
                INSERT INTO recipe (name, description, ingredients, instructions)
                VALUES (:name, :description, :ingredients, :instructions)
            ';
            $stmt = $conn->prepare($sql);
            $stmt->bindValue('name', $recipe->getName(), ParameterType::STRING);
            $stmt->bindValue('description', $recipe->getDescription(), ParameterType::STRING);
            $stmt->bindValue('ingredients', $recipe->getIngredients(), ParameterType::STRING);
            $stmt->bindValue('instructions', $recipe->getInstructions(), ParameterType::STRING);
            $stmt->executeStatement();

            $recipeId = $conn->lastInsertId();

            return $this->redirectToRoute('app_home', ['id' => $recipeId]);
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
    public function show(string $name): Response
    {
        $conn = $this->em->getConnection();
        $sql = 'SELECT * FROM recipe WHERE name = :name';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('name', $name, ParameterType::STRING);
        $result = $stmt->executeQuery();

        // Try both fetch() and fetchAssociative() for compatibility
        $recipe = method_exists($result, 'fetchAssociative') ? $result->fetchAssociative() : $result->fetch();

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
            $conn = $this->em->getConnection();
            $sql = '
            UPDATE recipe
            SET name = :name,
                description = :description,
                ingredients = :ingredients,
                instructions = :instructions
            WHERE id = :id
        ';
            $stmt = $conn->prepare($sql);
            $stmt->bindValue('name', $recipe->getName(), ParameterType::STRING);
            $stmt->bindValue('description', $recipe->getDescription(), ParameterType::STRING);
            $stmt->bindValue('ingredients', $recipe->getIngredients(), ParameterType::STRING);
            $stmt->bindValue('instructions', $recipe->getInstructions(), ParameterType::STRING);
            $stmt->bindValue('id', $recipe->getId(), ParameterType::INTEGER);
            $stmt->executeStatement();

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
        $conn = $this->em->getConnection();
        $sql = 'DELETE FROM recipe WHERE id = :id';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('id', $recipe->getId(), ParameterType::INTEGER);
        $stmt->executeStatement();

        return $this->redirectToRoute("app_home");
    }

    // /**
    //  * @param Recipe $recipe
    //  * @param Request $request
    //  * @return Response
    //  */
    // #[Route('/recipe/favorite/{recipe}', name: 'app_recipe_favorite')]
    // public function favoriteRecipe(Recipe $recipe, Request $request, RecipeService $recipeService, EntityManagerInterface $entityManager): Response
    // {
    //     $user = $this->getUser();
    //     if (!$user) {
    //         return $this->redirectToRoute('app_login');
    //     }

    //     $conn = $this->em->getConnection();

    //     // Obtenir l'ID de l'utilisateur
    //     // $userId = $user->getId();

    //     // // Vérifier si la recette est déjà dans les favoris de l'utilisateur
    //     // $sql = 'SELECT COUNT(*) FROM favorite_recipe WHERE user_id = :userId AND recipe_id = :recipeId';
    //     // $stmt = $conn->prepare($sql);
    //     // $stmt->bindValue('userId', $userId, ParameterType::INTEGER);
    //     // $stmt->bindValue('recipeId', $recipe->getId(), ParameterType::INTEGER);
    //     // $stmt->executeQuery();
    //     // $count = (int) $stmt->fetchColumn();

    //     // if ($count === 0) {
    //         // Si la recette n'est pas encore dans les favoris, l'ajouter
    //         $sql = 'INSERT INTO favorite_recipe (user_id, recipe_id) VALUES (:userId, :recipeId)';
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bindValue('userId', $user->getUserId(), ParameterType::INTEGER);
    //         $stmt->bindValue('recipeId', $recipe->getId(), ParameterType::INTEGER);
    //         $stmt->executeStatement();

    //         $this->addFlash('success', 'La recette a été ajoutée aux favoris.');
    //     // } else {
    //     //     $this->addFlash('warning', 'La recette est déjà dans les favoris.');
    //     // }

    //     return $this->redirectToRoute('app_recipe_id', [
    //         'id' => $recipe->getId(),
    //         'name' => $recipe->getName(),
    //         'message' => 'recipe_favorite'
    //     ]);
    // }

    // /**
    //  * @param Recipe $recipe
    //  * @param Request $request
    //  * @return Response
    //  */
    // #[Route('/recipe/collection/{recipe}', name: 'app_recipe_collection')]
    // public function collection(Recipe $recipe, Request $request, EntityManagerInterface $entityManager): Response
    // {
    //     $user = $this->getUser();
    //     if (!$user) {
    //         return $this->redirectToRoute('app_login');
    //     }

    //     $conn = $this->em->getConnection();

    //     // Récupération de la collection associée à l'utilisateur courant et à la recette en question
    //     $userId = $user->getId();
    //     $recipeId = $recipe->getId();

    //     $sql = 'SELECT * FROM recipe_collection WHERE user_id = :userId AND recipe_id = :recipeId';
    //     $stmt = $conn->prepare($sql);
    //     $stmt->bindValue('userId', $userId, ParameterType::INTEGER);
    //     $stmt->bindValue('recipeId', $recipeId, ParameterType::INTEGER);
    //     $stmt->executeQuery();
    //     $recipeCollection = $stmt->fetchAssociative();


    //     // Création d'un nouveau formulaire pour ajouter la recette à une collection
    //     $form = $this->createForm(RecipeCollectionType::class);

    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         // Récupération des données du formulaire
    //         $data = $form->getData();

    //         // Création d'une nouvelle collection si elle n'existe pas déjà
    //         if (!$recipeCollection) {
    //             $sql = 'INSERT INTO recipe_collection (user_id, recipe_id, name) VALUES (:userId, :recipeId, :name)';
    //             $stmt = $conn->prepare($sql);
    //             $stmt->bindValue('userId', $userId, ParameterType::INTEGER);
    //             $stmt->bindValue('recipeId', $recipeId, ParameterType::INTEGER);
    //             $stmt->bindValue('name', $data['name'], ParameterType::STRING);
    //             $stmt->executeStatement();
    //         }

    //         // Ajout de la recette à la collection
    //         $sql = 'INSERT INTO recipe_collection_recipe (collection_id, recipe_id) VALUES (:collectionId, :recipeId)';
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bindValue('collectionId', $recipeCollection['id'], ParameterType::INTEGER);
    //         $stmt->bindValue('recipeId', $recipeId, ParameterType::INTEGER);
    //         $stmt->executeStatement();

    //         $this->addFlash('success', 'Recipe added to collection.');

    //         return $this->redirectToRoute('app_recipe_collection', [
    //             'recipe' => $recipeId,
    //         ]);
    //     }

    //     return $this->render('recipe/collection.html.twig', [
    //         'form' => $form->createView(),
    //         'recipe' => $recipe,
    //         'recipeCollection' => $recipeCollection,
    //     ]);
    // }
}
