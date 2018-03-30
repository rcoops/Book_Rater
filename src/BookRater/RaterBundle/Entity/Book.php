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
 *   "self",
 *   href=@Hateoas\Route(
 *     "api_books_show",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "authors",
 *   href=@Hateoas\Route(
 *     "api_books_authors_list",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "reviews",
 *   href=@Hateoas\Route(
 *     "api_books_reviews_list",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation("google_books", href="expr(object.getGoogleBooksUrl())")
 * @Hateoas\Relation("google_reviews", href="expr(object.getGoogleBooksReviewsUrl())")
 *
 * @ORM\Table(name="books", uniqueConstraints={@UniqueConstraint(name="unique_book", columns={"title", "edition"})})
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\BookRepository")
 * @ORM\EntityListeners({"BookRater\RaterBundle\EventListener\BookListener"})
 *
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\XmlRoot("book")
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
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin"})
     * @Serializer\XmlElement(cdata=false)
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
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin", "books_send"})
     * @Serializer\XmlElement(cdata=false)
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
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin", "books_send"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(
     *   description="The book's unique 10 digit International Standard Book Number.",
     *   example={"0123456789"}
     * )
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
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin", "books_send"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(
     *   description="The book's unique 13 digit International Standard Book Number.",
     *   example={"9780123456789"}
     * )
     */
    private $isbn13 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin", "books_send"})
     * @Serializer\XmlElement(cdata=false)
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
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin", "books_send"})
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="The date that this publication of the book was published.", example="1990-10-25")
     *
     */
    private $publishDate = null;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin", "books_send"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="The edition number for this publication of the book.")
     */
    private $edition = null;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin", "books_send"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="A short synopsis of the book.")
     */
    private $description;

    /**
     * @var Collection|Author[]
     *
     * @ORM\ManyToMany(targetEntity="Author", mappedBy="booksAuthored")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"books", "reviews", "messages"})
     * @Serializer\XmlList(entry="author")
     *
     * @SWG\Property(description="The book's author or authors.")
     */
    private $authors;

    /**
     * @var Collection|Review[]
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="book", cascade={"remove"})
     *
     * @Serializer\Expose
     * @Serializer\Groups({"books", "authors", "messages", "admin"})
     * @Serializer\XmlList(entry="review")
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
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="The book's average rating from 1 to 5 based on its reviews.", minimum="1", maximum="5")
     */
    private $averageRating = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="google_books_id", type="string", nullable=true, unique=true)
     */
    private $googleBooksId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="google_books_url", type="string", nullable=true)
     *
     * @SWG\Property(description="A url to google books for this book")
     */
    private $googleBooksUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(name="google_books_reviews_url", type="string", nullable=true)
     *
     * @SWG\Property(description="A url to the google books reviews for this book")
     */
    private $googleBooksReviewsUrl;

    /**
     * @var int|null
     *
     * @Serializer\Expose
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @ORM\Column(name="google_books_rating", type="integer", nullable=true)
     *
     * @SWG\Property(description="The book's average rating from 1 to 5 based on its reviews.", minimum="1", maximum="5")
     */
    private $googleBooksRating;

    /**
     * @Serializer\SerializedName("_links")
     * @Serializer\Expose
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin"})
     *
     * @SWG\Property(
     *   type="object",
     *   description="A series of resource urls conforming to application/hal+json standards",
     *   @SWG\Property(type="string", property="self", description="A relative url to this resource."),
     *   @SWG\Property(
     *     type="string",
     *     property="authors",
     *     description="A relative url to the authors associated with this resource.",
     *   ),
     *   @SWG\Property(
     *     type="string",
     *     property="reviews",
     *     description="A relative url to the reviews associated with this resource.",
     *   ),
     * )
     */
    // This is a fake property and will be overridden dynamically during serialisation - here for swagger's benefit
    private $links;

    /**
     * @Serializer\Expose
     * @Serializer\Groups({"books_send"})
     * @Serializer\XmlList(entry="author")
     *
     * @SWG\Property(
     *   type="array",
     *   description="A collection of existing author ids belonging to the authors of the book.",
     *   @SWG\Items(type="integer", description="An existing author id.")
     * )
     */
    // This is a fake property and will be overridden dynamically during serialisation - here for swagger's benefit
    private $authorIds;

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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle(): ?string
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
    public function setTitle(string $title): Book
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get isbn
     *
     * @return string|null
     */
    public function getIsbn(): ?string
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
    public function setIsbn(string $isbn): Book
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Get isbn13
     *
     * @return string|null
     */
    public function getIsbn13(): ?string
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
    public function setIsbn13(?string $isbn13): Book
    {
        $this->isbn13 = $isbn13;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return string|null
     */
    public function getPublisher(): ?string
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
    public function setPublisher(?string $publisher): Book
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publishDate
     *
     * @return DateTime|null
     */
    public function getPublishDate(): ?DateTime
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
    public function setPublishDate(?DateTime $publishDate): Book
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * Get edition
     *
     * @return int|null
     */
    public function getEdition(): ?int
    {
        return $this->edition;
    }

    /**
     * @return string
     */
    public function displayEdition(): string
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
    public function setEdition(?int $edition): Book
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get authors
     *
     * @return Collection|Author[]
     */
    public function getAuthors(): Collection
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
    public function addAuthor(Author $author): Book
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
    public function removeAuthor(Author $author): Book
    {
        $author->removeBooksAuthored($this);

        return $this;
    }

    /**
     * Get reviews
     *
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
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
    public function addReview(Review $review): Book
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
    public function removeReview(Review $review): Book
    {
        $this->reviews->removeElement($review);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAverageRating(): ?int
    {
        return $this->averageRating;
    }

    /**
     * @param float|null $averageRating
     *
     * @return Book
     */
    public function setAverageRating(?float $averageRating): Book
    {
        $this->averageRating = (int)$averageRating;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGoogleBooksId(): ?string
    {
        return $this->googleBooksId;
    }

    /**
     * @param null|string $googleBooksId
     * @return Book
     */
    public function setGoogleBooksId(?string $googleBooksId): Book
    {
        $this->googleBooksId = $googleBooksId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getGoogleBooksRating(): ?int
    {
        return $this->googleBooksRating;
    }

    /**
     * @param int|null $googleBooksRating
     * @return Book
     */
    public function setGoogleBooksRating(?int $googleBooksRating): Book
    {
        $this->googleBooksRating = $googleBooksRating;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Book
     */
    public function setDescription(?string $description): Book
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGoogleBooksUrl(): ?string
    {
        return $this->googleBooksUrl;
    }

    /**
     * @param string|null $googleBooksUrl
     * @return Book
     */
    public function setGoogleBooksUrl(?string $googleBooksUrl): Book
    {
        $this->googleBooksUrl = $googleBooksUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGoogleBooksReviewsUrl(): ?string
    {
        return $this->googleBooksReviewsUrl;
    }

    /**
     * @param null|string $googleBooksReviewsUrl
     * @return Book
     */
    public function setGoogleBooksReviewsUrl(?string $googleBooksReviewsUrl): Book
    {
        $this->googleBooksReviewsUrl = $googleBooksReviewsUrl;

        return $this;
    }

    /**
     * @return string
     *
     * @Serializer\Expose
     * @Serializer\Groups({"authors", "admin"})
     * @Serializer\VirtualProperty(name="authors")
     * @Serializer\SerializedName("authors")
     * @Serializer\XmlList(entry="author")
     */
    public function getAuthorNames()
    {
        return $this->authors->map(function (Author $author) {
            return $author->getLastName() . ", " . $author->getFirstName();
        });
    }

    /**
     * @return string|null
     */
    public function __toString(): ?string
    {
        return $this->title;
    }

}
