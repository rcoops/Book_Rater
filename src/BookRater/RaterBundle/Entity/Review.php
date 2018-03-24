<?php

namespace BookRater\RaterBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @ORM\Table(name="reviews")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\ReviewRepository")
 * @ORM\EntityListeners({"BookRater\RaterBundle\EventListener\ReviewListener"})
 *
 * @Serializer\ExclusionPolicy("all")
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
     *
     * @SWG\Property(description="The unique identifier of the review.")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Review title must be entered.")
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="A short description summarising the review.")
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotNull(message="Review comments can not be null.")
     *
     * @ORM\Column(type="text")
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="A commentary of the book being reviewed.")
     */
    private $comments;

    /**
     * @var int
     *
     * @Assert\Range(min="1", max="5", minMessage="Rating must be at least 1.",
     *     maxMessage="Rating must be no more than 5.", invalidMessage="Rating must be numeric.")
     *
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The review's rating of the book from 1 to 5.")
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
     * @Serializer\Groups({"reviews"})
     *
     * @SWG\Property(description="The book being reviewed.")
     */
    private $book;

    /**
     * @var User
     *
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reviews")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"reviews"})
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
     *
     * @SWG\Property(description="The date and time on which the review was last edited.")
     */
    private $edited;

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
    public function setRating(int $rating) : Review
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
     * @return string
     */
    public function __toString() : string
    {
        return $this->title;
    }

}
