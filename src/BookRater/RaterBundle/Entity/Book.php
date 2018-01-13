<?php

namespace BookRater\RaterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Book
 *
 * @ORM\Table(name="book")
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
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Book title must be entered")
     * @ORM\Column(name="title", type="string", length=255)
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
     * @ORM\Column(name="isbn", type="string", unique=true)
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
     */
    private $isbn13;

    /**
     * @var string
     *
     * @ORM\Column(name="publisher", type="string", length=255, nullable=true)
     */
    private $publisher;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_date", type="datetime", nullable=true)
     */
    private $publishDate;

    /**
     * @var int
     *
     * @ORM\Column(name="edition", type="integer", nullable=true)
     */
    private $edition;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Author", mappedBy="booksAuthored", cascade={"persist"})
     */
    private $authors;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="bookReviewed")
     */
    private $reviews;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Book
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get isbn13
     *
     * @return string
     */
    public function getIsbn13()
    {
        return $this->isbn13;
    }

    /**
     * Set publisher
     *
     * @param string $publisher
     *
     * @return Book
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set publishDate
     *
     * @param \DateTime $publishDate
     *
     * @return Book
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * Get publishDate
     *
     * @return \DateTime
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Set edition
     *
     * @param integer $edition
     *
     * @return Book
     */
    public function setEdition($edition)
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get edition
     *
     * @return int
     */
    public function getEdition()
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
    public function addAuthor(Author $author)
    {
        dump($author); die;
        $author->addBookAuthored($this);
        $this->authors[] = $author;

        return $this;
    }

    /**
     * Remove author
     *
     * @param Author $author
     */
    public function removeAuthor(Author $author)
    {
        $author->removeBooksAuthored($this);
    }

    /**
     * Get authors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthors()
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
    public function addReview(Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param Review $review
     */
    public function removeReview(Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * Get reviews
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Set isbn
     *
     * @param string $isbn
     *
     * @return Book
     */
    public function setIsbn(string $isbn)
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Get isbn
     *
     * @return string
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * Set isbn13
     *
     * @param string $isbn13
     *
     * @return Book
     */
    public function setIsbn13(string $isbn13)
    {
        $this->isbn13 = $isbn13;

        return $this;
    }

}
