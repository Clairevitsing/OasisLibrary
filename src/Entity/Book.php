<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $ISBN = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $publishedYear = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'Book')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $categoryId = null;

    #[ORM\ManyToOne(inversedBy: 'book')]
    private ?Loan $loan = null;

    /**
     * @var Collection<int, BookAuthor>
     */
    #[ORM\OneToMany(targetEntity: BookAuthor::class, mappedBy: 'book', orphanRemoval: true)]
    private Collection $bookAuthors;

    #[ORM\Column]
    private ?bool $available = null;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->bookAuthors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getISBN(): ?string
    {
        return $this->ISBN;
    }

    public function setISBN(string $ISBN): static
    {
        $this->ISBN = $ISBN;

        return $this;
    }

    public function getPublishedYear(): ?\DateTimeInterface
    {
        return $this->publishedYear;
    }

    public function setPublishedYear(\DateTimeInterface $publishedYear): static
    {
        $this->publishedYear = $publishedYear;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCategoryId(): ?Category
    {
        return $this->categoryId;
    }

    public function setCategoryId(?Category $categoryId): static
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
            $author->addBook($this);
        }

        return $this;
    }

    public function removeAuthor(Author $author): static
    {
        if ($this->authors->removeElement($author)) {
            $author->removeBook($this);
        }

        return $this;
    }

    public function getLoan(): ?Loan
    {
        return $this->loan;
    }

    public function setLoan(?Loan $loan): static
    {
        $this->loan = $loan;

        return $this;
    }

    /**
     * @return Collection<int, BookAuthor>
     */
    public function getBookAuthors(): Collection
    {
        return $this->bookAuthors;
    }

    public function addBookAuthor(BookAuthor $bookAuthor): static
    {
        if (!$this->bookAuthors->contains($bookAuthor)) {
            $this->bookAuthors->add($bookAuthor);
            $bookAuthor->setBook($this);
        }

        return $this;
    }

    public function removeBookAuthor(BookAuthor $bookAuthor): static
    {
        if ($this->bookAuthors->removeElement($bookAuthor)) {
            // set the owning side to null (unless already changed)
            if ($bookAuthor->getBook() === $this) {
                $bookAuthor->setBook(null);
            }
        }

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): static
    {
        $this->available = $available;

        return $this;
    }
}
