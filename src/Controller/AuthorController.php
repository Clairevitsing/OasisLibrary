<?php

namespace App\Controller;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AuthorRepository;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/authors')]
class AuthorController extends AbstractController
{
    public function __construct(
        private AuthorRepository $authorRepository
    ){
    }
    #[Route('/', name: 'author_index', methods: ['GET'])]
    public function index(AuthorRepository $authorRepository): JsonResponse
    {
        $authors = $authorRepository->findAll();
        dd($authors);
        return $this->json($authors, context: ['groups' => 'author:read']);
    }

    #[Route('/{id}', name: 'author_read', methods: ['GET'])]
    public function read(int $id, AuthorRepository $authorRepository): JsonResponse
    {
        $author = $authorRepository->findOneById($id);
        if (!$author) {
            throw $this->createNotFoundException('Author not found');
        }
        //dd($author);
        return $this->json($author, context: ['groups' => 'author:read']);
    }

    #[Route('/', name: 'author_create', methods: ['POST'])]
    public function create(Request $request, AuthorRepository $authorRepository): JsonResponse
    {
        try {
            // Retrieve the data sent from Postman
            $data = json_decode($request->getContent(), true);

            // Validation of required data
            $requiredFields = ['name', 'averageSize', 'averageLifespan', 'martialArt', 'phoneNumber', 'countryIds'];
            foreach ($requiredFields as $field) {
                // Check if each required field is present in the data
                if (!isset($data[$field])) {
                    // If any required field is missing, throw a Bad Request exception
                    return new JsonResponse(['message' => "Missing required field: $field"], Response::HTTP_BAD_REQUEST);
                }
            }

            // Call the create method of AuthorRepository to create the animal
            $author = $this->authorManager->create($data);

            // Return a JSON response indicating successful creation of the author
            return new JsonResponse($authorRepository->findOneById($author->getId()), Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route('/{id}', name: 'author_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request,AuthorRepository $authorRepository): JsonResponse
    {
        // Retrieve the animal to edit using the AuthorRepository
        $author = $authorRepository->find($id);


        // Check if the animal exists
        if (!$author instanceof Author) {
            // If the author is not found, return a JSON response with an error message
            return new JsonResponse(['message' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        //dd($author);
        // Retrieve the data sent from Postman
        $data = json_decode($request->getContent(), true);
        //dd($data);

        // Call the update method in the animal manager to update the animal
        try {
            $updatedAuthor = $this->authorManager->update($author,$data);
        } catch (\InvalidArgumentException $e) {
            // Handle invalid argument exceptions
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        }

        // Return a JSON response indicating success
        return new JsonResponse($authorRepository->findOneById($updatedAuthor->getId()), Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'author_delete', methods: ['DELETE'])]
    public function delete(int $id, Request $request, AuthorRepository $authorRepository, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the author to delete using the AnimalRepository
        $author = $authorRepository->find($id);

        // Check if the animal exists
        if (!$author) {
            return new JsonResponse(['message' => 'Animal not found'], Response::HTTP_NOT_FOUND);
        }

        // Use the repository's remove method to delete the animal
        $authorRepository->remove($author);

        // Return a JSON response indicating success
        return new JsonResponse(['message' => 'Author is deleted successfully'], Response::HTTP_OK);
    }

}
