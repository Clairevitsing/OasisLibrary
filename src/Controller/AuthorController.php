<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AuthorRepository;

#[Route('api/authors')]
class AuthorController extends AbstractController
{
    public function __construct(
        private AuthorRepository $authorRepository,
        private EntityManagerInterface $entityManager
    ){
    }
    #[Route('/', name: 'author_index', methods: ['GET'])]
    public function index(AuthorRepository $authorRepository): JsonResponse
    {
        $authors = $authorRepository->findAll();
        //dd($authors);
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

    #[Route('/new', name: 'author_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $content = $request->getContent();
        if (empty($content)) {
            return $this->json(['error' => 'No data provided'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // Check that all required fields are present
        if (!isset($data['firstName'], $data['lastName'], $data['biography'], $data['birthDate'], $data['books'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $author = new Author();
        $author->setFirstName($data['firstName']);
        $author->setLastName($data['lastName']);
        $author->setBiography($data['biography']);
        $author->setBirthDate(new \DateTime($data['birthDate']));

        // Preload all categories
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category->getName()] = $category; // Map category names to category objects
        }

        foreach ($data['books'] as $bookData) {
            if (!isset($bookData['title'], $bookData['category'])) {
                return $this->json(['error' => 'Book title and category are required'], Response::HTTP_BAD_REQUEST);
            }

            // Check if the book already exists
            $book = $this->entityManager->getRepository(Book::class)->findOneBy(['title' => $bookData['title']]);

            if (!$book) {
                $book = new Book();
                $book->setTitle($bookData['title']);
                $book->setISBN($bookData['ISBN'] ?? null);

                if (isset($bookData['publishedYear'])) {
                    $publishedDate = new \DateTime();
                    $publishedDate->setDate($bookData['publishedYear'], 1, 1);
                    $book->setPublishedYear($publishedDate);
                }

                $book->setDescription($bookData['description'] ?? null);
                $book->setImage($bookData['image'] ?? null);

                // Check and get or create Category
                $categoryName = $bookData['category'];
                if (!isset($categoryMap[$categoryName])) {
                    $category = new Category();
                    $category->setName($categoryName);
                    $category->setDescription($bookData['categoryDescription'] ?? null);
                    // Persist new category
                    $this->entityManager->persist($category);
                    // Add to category map
                    $categoryMap[$categoryName] = $category;
                }
                $book->setCategory($categoryMap[$categoryName]); // Set the book's category

                $book->setAvailable($bookData['available'] ?? false);

                $this->entityManager->persist($book);
            }

            // Add author to the book and book to the author
            $book->addAuthor($author);
            $author->addBook($book);
        }

        $this->entityManager->persist($author); // Persist the author
        $this->entityManager->flush(); // Flush all changes to the database

        return $this->json([
            'message' => 'Author created successfully',
            'id' => $author->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'author_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
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
