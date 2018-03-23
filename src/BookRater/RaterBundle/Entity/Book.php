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

/**
 * Book
 *
 * @Serializer\ExclusionPolicy("all")
 *
 * @ORM\Table(name="book", uniqueConstraints={@UniqueConstraint(name="unique_book", columns={"title", "edition"})})
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\BookRepository")
 */
class Book
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Expose
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Book title must be entered")
     *
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9]{10}$/",
     *     match=true,
     *     message="ISBN must be a combination of 10 digits/characters"
     * )
     *
     * @ORM\Column(name="isbn", type="string", unique=true)
     *
     * @Serializer\Expose
     */
    private $isbn;

    /**
     * @var string
     *
     * @Assert\Regex(
     *     pattern="/^\d{3}-\d{10}$/",
     *     match=true,
     *     message="ISBN 13 must be a 13 digit number"
     * )
     * @ORM\Column(name="isbn_13", type="string", nullable=true, unique=true)
     *
     * @Serializer\Expose
     */
    private $isbn13;

    /**
     * @var string
     *
     * @ORM\Column(name="publisher", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     */
    private $publisher;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="publish_date", type="datetime", nullable=true)
     *
     * @Serializer\Expose
     */
    private $publishDate;

    /**
     * @var int
     *
     * @ORM\Column(name="edition", type="integer", nullable=true)
     *
     * @Serializer\Expose
     */
    private $edition;

    /**
     * @var Collection|Author[]
     *
     * @ORM\ManyToMany(targetEntity="Author", mappedBy="booksAuthored")
     *
     * @Serializer\Groups({"books"})
     * @Serializer\Expose
     */
    private $authors;

    /**
     * @var Collection|Review[]
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="bookReviewed", cascade={"remove"})
     *
     * @Serializer\Groups({"books"})
     * @Serializer\Expose
     */
    private $reviews;

    /**
     * @var null|int
     *
     * @ORM\Column(name="average_rating", type="integer", nullable=true)
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
     * Set title
     *
     * @param null|string $title
     *
     * @return Book
     */
    public function setTitle(?string $title) : Book
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return null|string
     */
    public function getTitle() : ?string
    {
        return $this->title;
    }

    /**
     * Get isbn13
     *
     * @return null|string
     */
    public function getIsbn13() : ?string
    {
        return $this->isbn13;
    }

    /**
     * Set publisher
     *
     * @param null|string $publisher
     *
     * @return Book
     */
    public function setPublisher(?string $publisher) : Book
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return null|string
     */
    public function getPublisher() : ?string
    {
        return $this->publisher;
    }

    /**
     * Set publishDate
     *
     * @param null|DateTime $publishDate
     *
     * @return Book
     */
    public function setPublishDate(?DateTime $publishDate) : Book
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * Get publishDate
     *
     * @return null|DateTime
     */
    public function getPublishDate() : ?DateTime
    {
        return $this->publishDate;
    }

    /**
     * Set edition
     *
     * @param null|int $edition
     *
     * @return Book
     */
    public function setEdition(?int $edition) : Book
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get edition
     *
     * @return null|int
     */
    public function getEdition() : ?int
    {
        return $this->edition;
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
     * Get authors
     *
     * @return Collection|Author[]
     */
    public function getAuthors() : Collection
    {
        return $this->authors;
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
     * Get reviews
     *
     * @return Collection|Review[]
     */
    public function getReviews() : Collection
    {
        return $this->reviews;
    }

    /**
     * Set isbn
     *
     * @param null|string $isbn
     *
     * @return Book
     */
    public function setIsbn(?string $isbn) : Book
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Get isbn
     *
     * @return null|string
     */
    public function getIsbn() : ?string
    {
        return $this->isbn;
    }

    /**
     * Set isbn13
     *
     * @param null|string $isbn13
     *
     * @return Book
     */
    public function setIsbn13(?string $isbn13) : Book
    {
        $this->isbn13 = $isbn13;

        return $this;
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
     * @return int|null
     */
    public function getAverageRating() : ?int
    {
        return $this->averageRating;
//        $reviewRatings = $this->reviews
//            ->map(function (Review $review) {
//                return $review->getRating();
//            })
//            ->toArray();
//        return $reviewRatings ? array_sum($reviewRatings) / count($reviewRatings) : null;
    }

    /**
     * @param null|float $averageRating
     *
     * @return Book
     */
    public function setAverageRating(?float $averageRating) : Book
    {
        $this->averageRating = (int) $averageRating;

        return $this;
    }

    /**
     * @return null|string
     */
    public function __toString() : ?string
    {
        return $this->title;
    }

}
