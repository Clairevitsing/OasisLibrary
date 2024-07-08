<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTime;


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

    #[ORM\ManyToOne(inversedBy: 'Books')]
    #[ORM\JoinColumn(name: "category_id", nullable: false, referencedColumnName: "id")]
    private ?Category $category = null;

    /**
     * @var Collection<int, BookAuthor>
     */
    #[ORM\OneToMany(targetEntity: BookAuthor::class, mappedBy: 'book', orphanRemoval: true)]
    private Collection $bookAuthors;

    #[ORM\Column]
    private ?bool $available = null;

    /**
     * @var Collection<int, BookLoan>
     */
    #[ORM\OneToMany(targetEntity: BookLoan::class, mappedBy: 'book')]
    private Collection $bookLoans;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->bookAuthors = new ArrayCollection();
        $this->bookLoans = new ArrayCollection();
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

    public function setPublishedYear(\DateTimeInterface $publishedYear): self
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

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

    /**
     * @return Collection<int, BookLoan>
     */
    public function getBookLoans(): Collection
    {
        return $this->bookLoans;
    }

    public function addBookLoan(BookLoan $bookLoan): static
    {
        if (!$this->bookLoans->contains($bookLoan)) {
            $this->bookLoans->add($bookLoan);
            $bookLoan->setBook($this);
        }

        return $this;
    }

    public function removeBookLoan(BookLoan $bookLoan): static
    {
        if ($this->bookLoans->removeElement($bookLoan)) {
            // set the owning side to null (unless already changed)
            if ($bookLoan->getBook() === $this) {
                $bookLoan->setBook(null);
            }
        }

        return $this;
    }
}
