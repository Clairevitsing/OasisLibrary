<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    const NBCATEGORIES = 6;
    const NBBOOKS = 30;
    const NBAUTEURS = 10;


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création des catégories
        $categories = [];
        for ($i = 0; $i < self::NBCATEGORIES; $i++) {
            $category = new Category();
            $category->setName($faker->word());
            $category->setDescription($faker->text(300));
            $manager->persist($category);
            $categories[] = $category;
        }

        // Création des livres
        for ($i = 0; $i < self::NBBOOKS; $i++) {
            $book = new Book();
            $book->setTitle($faker->sentence(3));
            $book->setDescription($faker->text(500));
            $book->setPublicationDate($faker->dateTimeImmutable('-30 years', 'now'));
            $book->setAvailable($faker->boolean(80)); // 80% de chance d'être disponible

            // Ajout de l'ISBN
            $book->setIsbn($this->generateIsbn($faker));

            // Ajout de l'image
            $book->setImage($this->getRandomBookCover($faker));

            // Assignation aléatoire d'une catégorie
            $book->setCategory($faker->randomElement($categories));

            $manager->persist($book);
        }

        $manager->flush();
    }

    private function generateIsbn(Faker\Generator $faker): string
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

    private function getRandomBookCover(Faker\Generator $faker): string
    {
        // Utilise le service d'images de placeholder.com pour générer des couvertures aléatoires
        $width = 300;
        $height = 400;
        $backgroundColor = $faker->hexColor();
        $textColor = $faker->hexColor();
        $text = urlencode('Book ' . $faker->word());

        return "https://via.placeholder.com/{$width}x{$height}/{$backgroundColor}/{$textColor}?text={$text}";
    }
}
