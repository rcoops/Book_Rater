<?php

namespace BookRater\RaterBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "self",
 *     href=@Hateoas\Route(
 *       "api_books_show",
 *       parameters = { "id" = "expr(object.getId())" }
 *     )
 * )
 * @Hateoas\Relation(
 *     "authors",
 *     href=@Hateoas\Route(
 *         "api_books_authors_list",
 *         parameters = { "id" = "expr(object.getId())" }
 *     )
 * )
 * @Hateoas\Relation(
 *     "reviews",
 *     href=@Hateoas\Route(
 *         "api_books_reviews_list",
 *         parameters = { "id" = "expr(object.getId())" }
 *     )
 * )
 *
 * @ORM\Table(name="books", uniqueConstraints={@UniqueConstraint(name="unique_book", columns={"title", "edition"})})
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\BookRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Book
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The unique identifier of the book.")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Book title must be entered.")
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The book's full title.")
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="ISBN must be entered.")
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9]{10}$/",
     *     match=true,
     *     message="ISBN must be a combination of 10 digits/characters"
     * )
     *
     * @ORM\Column(type="string", length=15, unique=true)
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The book's unique 10 digit International Standard Book Number.")
     */
    private $isbn;

    /**
     * @var string|null
     *
     * @Assert\Regex(
     *     pattern="/^\d{13}$/",
     *     match=true,
     *     message="ISBN 13 must 13 digits."
     * )
     *
     * @ORM\Column(name="isbn_13", type="string", length=15, nullable=true, unique=true)
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The book's unique 13 digit International Standard Book Number.")
     */
    private $isbn13 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The company that published this edition of the book.")
     */
    private $publisher = null;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The date that this publication of the book was published.")
     */
    private $publishDate = null;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The edition number for this publication of the book.")
     */
    private $edition = null;

    /**
     * @var Collection|Author[]
     *
     * @ORM\ManyToMany(targetEntity="Author", mappedBy="booksAuthored")
     *
     * @Serializer\Groups({"books"})
     * @Serializer\Expose
     *
     * @SWG\Property(description="The book's author or authors.")
     */
    private $authors;

    /**
     * @var Collection|Review[]
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="book", cascade={"remove"})
     *
     * @Serializer\Groups({"books"})
     * @Serializer\Expose
     *
     * @SWG\Property(description="All site reviews of this book.")
     */
    private $reviews;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The book's average rating from 1 to 5 based on its reviews.")
     */
    private $averageRating = null;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle() : ?string
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Book
     */
    public function setTitle(string $title) : Book
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get isbn
     *
     * @return string|null
     */
    public function getIsbn() : ?string
    {
        return $this->isbn;
    }

    /**
     * Set isbn
     *
     * @param string $isbn
     *
     * @return Book
     */
    public function setIsbn(string $isbn) : Book
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Get isbn13
     *
     * @return string|null
     */
    public function getIsbn13() : ?string
    {
        return $this->isbn13;
    }

    /**
     * Set isbn13
     *
     * @param string|null $isbn13
     *
     * @return Book
     */
    public function setIsbn13(?string $isbn13) : Book
    {
        $this->isbn13 = $isbn13;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return string|null
     */
    public function getPublisher() : ?string
    {
        return $this->publisher;
    }

    /**
     * Set publisher
     *
     * @param string|null $publisher
     *
     * @return Book
     */
    public function setPublisher(?string $publisher) : Book
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publishDate
     *
     * @return DateTime|null
     */
    public function getPublishDate() : ?DateTime
    {
        return $this->publishDate;
    }

    /**
     * Set publishDate
     *
     * @param DateTime|null $publishDate
     *
     * @return Book
     */
    public function setPublishDate(?DateTime $publishDate) : Book
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * Get edition
     *
     * @return int|null
     */
    public function getEdition() : ?int
    {
        return $this->edition;
    }

    /**
     * @return string
     */
    public function displayEdition() : string
    {
        $postFix = 'th';
        switch ($this->edition) {
            case 1:
                $postFix = 'st';
                break;
            case 2:
                $postFix = 'nd';
                break;
            case 3:
                $postFix = 'rd';
                break;
            default: //nothing
        }
        return $this->edition . $postFix . ' Edition';
    }

    /**
     * Set edition
     *
     * @param int|null $edition
     *
     * @return Book
     */
    public function setEdition(?int $edition) : Book
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get authors
     *
     * @return Collection|Author[]
     */
    public function getAuthors() : Collection
    {
        return $this->authors;
    }

    /**
     * Add author
     *
     * @param Author $author
     *
     * @return Book
     */
    public function addAuthor(Author $author) : Book
    {
        $author->addBooksAuthored($this);
        $this->authors[] = $author;

        return $this;
    }

    /**
     * Remove author
     *
     * @param Author $author
     *
     * @return Book
     */
    public function removeAuthor(Author $author) : Book
    {
        $author->removeBooksAuthored($this);

        return $this;
    }

    /**
     * Get reviews
     *
     * @return Collection|Review[]
     */
    public function getReviews() : Collection
    {
        return $this->reviews;
    }

    /**
     * Add review
     *
     * @param Review $review
     *
     * @return Book
     */
    public function addReview(Review $review) : Book
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param Review $review
     *
     * @return Book
     */
    public function removeReview(Review $review) : Book
    {
        $this->reviews->removeElement($review);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAverageRating() : ?int
    {
        return $this->averageRating;
    }

    /**
     * @param float|null $averageRating
     *
     * @return Book
     */
    public function setAverageRating(?float $averageRating) : Book
    {
        $this->averageRating = (int) $averageRating;

        return $this;
    }

    /**
     * @return string|null
     */
    public function __toString() : ?string
    {
        return $this->title;
    }

}
