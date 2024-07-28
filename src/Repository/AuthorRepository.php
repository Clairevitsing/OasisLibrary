<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }



    public function findAll(): array
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.id, a.firstName, a.lastName, a.biography, a.birthDate')
            ->getQuery();

        $authors = $query->getResult();

        // Récupérer les livres pour chaque auteur
        foreach ($authors as &$author) {
            $books = $this->createQueryBuilder('a')
                ->select('b.id, b.title')
                ->leftJoin('a.bookAuthors', 'ba')
                ->leftJoin('ba.book', 'b')
                ->where('a.id = :authorId')
                ->setParameter('authorId', $author['id'])
                ->getQuery()
                ->getResult();

            $author['books'] = $books;
        }

        return $authors;
    }
    public function findOneById(int $id): ?array
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.id, a.firstName, a.lastName, a.biography, a.birthDate')
            ->leftJoin('a.bookAuthors', 'ba')
            ->leftJoin('ba.book', 'b')
            ->addSelect('b.id as book_id, b.title as book_title')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        $result = $query->getResult();

        if (empty($result)) {
            return null;
        }

        $author = [
            'id' => $result[0]['id'],
            'firstName' => $result[0]['firstName'],
            'lastName' => $result[0]['lastName'],
            'biography' => $result[0]['biography'],
            'birthDate' => $result[0]['birthDate'],
            'books' => []
        ];

        foreach ($result as $row) {
            if ($row['book_id'] !== null) {
                $author['books'][] = [
                    'id' => $row['book_id'],
                    'title' => $row['book_title']
                ];
            }
        }

        return $author;
    }

}
