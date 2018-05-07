<?php

namespace BookRater\RaterBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *   "self",
 *   href=@Hateoas\Route(
 *     "api_reviews_show",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "user",
 *   href=@Hateoas\Route(
 *     "api_users_show",
 *     parameters = { "identifier" = "expr(object.getId())" }
 *   ),
 *   exclusion=@Hateoas\Exclusion(groups={"admin"})
 * )
 * @Hateoas\Relation(
 *   "book",
 *   href=@Hateoas\Route(
 *     "api_books_show",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 *
 * @ORM\Table(name="reviews")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\ReviewRepository")
 * @ORM\EntityListeners({"BookRater\RaterBundle\EventListener\ReviewListener"})
 *
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\XmlRoot("review")
 */
class Review
{

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews", "books", "authors", "messages", "admin"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="The unique identifier of the review.")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Title must be entered.")
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews", "books", "authors", "messages", "admin", "reviews_send"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="A short description summarising the review.")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews", "books", "authors", "messages", "admin", "reviews_send"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="A commentary of the book being reviewed.")
     */
    private $comments;

    /**
     * @var int
     *
     * @Assert\NotNull(message="Rating must be provided.")
     * @Assert\Range(min="1", max="5", minMessage="Rating must be at least 1.",
     *     maxMessage="Rating must be no more than 5.", invalidMessage="Rating must be numeric (1-5).")
     *
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews", "books", "authors", "messages", "admin", "reviews_send"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="The review's rating of the book from 1 to 5.", minimum="1", maximum="5")
     */
    private $rating;

    /**
     * @var Book
     *
     * @Assert\NotNull(message="Review cannot be created without an associated book.")
     *
     * @ORM\ManyToOne(targetEntity="Book", inversedBy="reviews")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews", "authors", "messages"})
     *
     * @SWG\Property(description="The book being reviewed.")
     */
    private $book;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reviews")
     *
     * @SWG\Property(description="The creator of the review.")
     */
    private $user;

    /**
     * @var DateTime
     *
     *
     * @ORM\Column(name="created_date", type="datetime")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews", "books", "authors", "messages", "admin"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="The date and time on which the review was created.")
     */
    private $created;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="edited_date", type="datetime", nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews", "books", "authors", "messages", "admin"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="The date and time on which the review was last edited.")
     */
    private $edited;

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
     *     property="user",
     *     description="A relative url to the book associated with this resource.",
     *   ),
     *   @SWG\Property(
     *     type="string",
     *     property="book",
     *     description="A relative url to the book associated with this resource.",
     *   ),
     * )
     */
    // This is a fake property and will be overridden dynamically during serialisation - here for swagger's benefit
    private $links;

    /**
     * @Serializer\Expose
     * @Serializer\Groups({"reviews_send"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(
     *   type="integer",
     *   description="The book id for which this book has been written.",
     * )
     */
    // This is a fake property and will be overridden dynamically during serialisation - here for swagger's benefit
    private $bookId;

    /**
     * Seperate from edited as we need a value initialised on creation or caching
     * @ORM\Column(name="last_modified", type="datetime")
     * @var DateTime
     */
    private $lastModified;

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle() : ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Review
     */
    public function setTitle(string $title) : Review
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string|null $comments
     *
     * @return Review
     */
    public function setComments(?string $comments) : Review
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRating() : ?int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     *
     * @return Review
     */
    public function setRating($rating) : Review
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return Book|null
     */
    public function getBook() : ?Book
    {
        return $this->book;
    }

    /**
     * @param Book $book
     *
     * @return Review
     */
    public function setBook(Book $book) : Review
    {
        $this->book = $book;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser() : ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Review
     */
    public function setUser(User $user) : Review
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreated() : ?DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     *
     * @return Review
     */
    public function setCreated(DateTime $created) : Review
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getEdited() : ?DateTime
    {
        return $this->edited;
    }

    /**
     * @param DateTime|null $edited
     *
     * @return Review
     */
    public function setEdited(?DateTime $edited) : Review
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastModified(): \DateTime
    {
        return $this->lastModified;
    }

    /**
     * @param mixed $lastModified
     * @return Review
     */
    public function setLastModified($lastModified): Review
    {
        $this->lastModified = $lastModified;
        $this->book->setLastModified($lastModified);

        return $this;
    }

    /**
     * @return string
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews", "books", "authors", "messages"})
     * @Serializer\VirtualProperty(name="user")
     * @Serializer\SerializedName("user")
     * @Serializer\XmlElement(cdata=false)
     */
    public function getUsername()
    {
        return $this->user->getUsername();
    }

    /**
     * @return string
     *
     * @Serializer\Expose
     * @Serializer\Groups({"books", "admin"})
     * @Serializer\VirtualProperty(name="book")
     * @Serializer\SerializedName("book")
     * @Serializer\XmlElement(cdata=false)
     */
    public function getBookName()
    {
        return $this->book->getTitle();
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->title;
    }

}
