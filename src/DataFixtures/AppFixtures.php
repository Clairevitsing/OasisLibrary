<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\BookAuthor;
use App\Entity\Category;
use App\Entity\Loan;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    // Constants for the number of entities to create
    const NB_CATEGORIES = 6;
    const NB_BOOKS = 30;
    const NB_AUTHORS = 10;
    const NB_USERS = 100;
    const NB_LOANS = 50;

    // User role constants
    const ROLE_USER = 'ROLE_USER';
    const ROLE_LIBRARIAN = 'ROLE_LIBRARIAN';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $categories = $this->createCategories($manager, $faker);
        $authors = $this->createAuthors($manager, $faker);
        $users = $this->createUsers($manager, $faker);
        $this->createBooks($manager, $faker, $categories, $authors, $users);

        $manager->flush();

    }

    private function createCategories(ObjectManager $manager,Generator $faker):array
    {
        // Création des catégories
        $categories = [];
        for ($i = 0; $i < self::NB_CATEGORIES; $i++) {
            $category = new Category();
            $category->setName($faker->unique()->word());
            $category->setDescription($faker->text(300));
            $manager->persist($category);
            $categories[] = $category;
        }
        return $categories;
    }

    //create authors
    private function createAuthors(ObjectManager $manager,Generator $faker):array
    {
        for ($i = 0; $i < self::NB_AUTHORS; $i++) {
            $author = new Author();
            $author->setFirstName($faker->firstname())
                ->setLastName($faker->lastname())
                ->setBiography($faker->text(200))
                ->setBirthDate($faker->dateTimeBetween('-75 years', '-18 years'));
            $manager->persist($author);
            $authors[] = $author;
        }
        return $authors;
    }

    //create users
    private array $usedUsernames = [];
    private function createUsers(ObjectManager $manager,Generator $faker):array
    {
        $users = [];
        $librarianCount = 0;
        // Ensure at least 1 librarian
        $targetLibrarianCount = max(1, ceil(self::NB_USERS / 20));

        for ($i = 0; $i < self::NB_USERS; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $username = $this->generateUniqueUsername($faker, $user->getFirstName(), $user->getLastName());
            $user->setUserName($username);
            $user->setPhoneNumber($faker->phoneNumber());
            $subStartDate = $faker->dateTimeBetween('-1 year', 'now');
            $user->setSubStartDate($subStartDate);
            $user->setSubEndDate($faker->dateTimeBetween($subStartDate, '+1 year'));

            if ($librarianCount < $targetLibrarianCount && ($i % 20 === 0 || $i === self::NB_USERS - 1)) {
                $user->setRoles([self::ROLE_LIBRARIAN]);
                $librarianCount++;
            } else {
                $user->setRoles([self::ROLE_USER]);
            }

            $user->setPassword("password");

            //$user->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));
            //$user->setIsActive(true);

            $manager->persist($user);
            $users[] = $user;
        }
        return $users;
    }

    //create books
    private function createBooks(ObjectManager $manager,Generator $faker,array $categories, array $authors, array $users):array
    {
        for ($i = 0; $i < self::NB_BOOKS; $i++) {
            $book = new Book();
            $book->setTitle($faker->sentence(3));
            $book->setDescription($faker->text(500));
            // Create a DateTimeImmutable object for the published year
            $publishedYear = new DateTimeImmutable($faker->date('Y-m-d', '-30 years'));
            $book->setPublishedYear($publishedYear);
            $book->setCategory($faker->randomElement($categories));
            // 80% de chance d'être disponible
            $book->setAvailable($faker->boolean(80));
            $book->addAuthor($authors[rand(0, self::NB_AUTHORS - 1)]);
            // add ISBN
            $book->setISBN($this->generateIsbn($faker));
            // add image
            $book->setImage($this->getRandomBookCover($faker));


            // Handle authors
            $numberOfAuthors = $faker->numberBetween(1, 3);
            $selectedAuthors = $faker->randomElements($authors, $numberOfAuthors);
            foreach ($selectedAuthors as $author) {
                $bookAuthor = new BookAuthor();
                $bookAuthor->setBook($book);
                $bookAuthor->setAuthor($author);
                $manager->persist($bookAuthor);
                $book->addBookAuthor($bookAuthor);
            }

            $manager->persist($book);
            $books[] = $book;
        }
        return $books;
    }

    private function generateIsbn(Generator $faker): string
    {
        // Génère un ISBN-13 valide
        $isbn = '978' . $faker->numerify('#########');
        $weightedSum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = intval($isbn[$i]);
            $weightedSum += ($i % 2 == 0) ? $digit : $digit * 3;
        }
        $checkDigit = (10 - ($weightedSum % 10)) % 10;
        return $isbn . $checkDigit;
    }

    private function getRandomBookCover(Generator $faker): string
    {
        $width = 300;
        $height = 400;
        $backgroundColor = $faker->hexColor();
        $textColor = $faker->hexColor();
        $text = urlencode('Book ' . $faker->word());

        return "https://via.placeholder.com/{$width}x{$height}/{$backgroundColor}/{$textColor}?text={$text}";
    }

    private function generateUniqueUsername(Generator $faker, string $firstName, string $lastName): string
    {
        // Start with the first letter of the first name and the full last name
        $username = strtolower($firstName[0] . $lastName);

        // Remove any non-alphanumeric characters
        $username = preg_replace('/[^a-z0-9]/', '', $username);

        // If this username is already taken, add random digits until it's unique
        while (in_array($username, $this->usedUsernames)) {
            $username .= $faker->randomDigit();
        }

        // Add this username to the list of used usernames
        $this->usedUsernames[] = $username;

        return $username;
    }


    private function createLoans(ObjectManager $manager, Generator $faker, array $books, array $users): void
    {
        for ($i = 0; $i < self::NB_LOANS; $i++) {
            $loan = new Loan();

            // Select a random book that is not already borrowed
            $availableBooks = array_filter($books, function($book) {
                return $book->isAvailable();
            });

            if (empty($availableBooks)) {
                continue; // If there are no available books, skip to the next iteration
            }

            $book = $faker->randomElement($availableBooks);
            $book->setAvailable(false);

            // Set loan date within the last 6 months
            $loanDate = $faker->dateTimeBetween('-6 months', 'now');

            // Set due date 3 weeks after loan date
            $dueDate = clone $loanDate;
            $dueDate->modify('+3 weeks');

            $loan->setLoanDate($loanDate);
            $loan->setDueDate($dueDate);
            $loan->addBook($book);
            $loan->setUser($faker->randomElement($users));

            // 70% chance that the book has been returned
            if ($faker->boolean(70)) {
                $returnDate = $faker->dateTimeBetween($loanDate, 'now');
                $loan->setReturnDate($returnDate);
                $book->setAvailable(true);
            }

            $manager->persist($loan);
        }
    }
}
