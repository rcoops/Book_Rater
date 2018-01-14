<?php

namespace BookRater\RaterBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Review
 *
 * @ORM\Table(name="review")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\ReviewRepository")
 */
final class Review
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
     * @ORM\Column(name="review_title", type="text")
     */
    private $reviewTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="reviewComments", type="text")
     */
    private $reviewComments;

    /**
     * @var int
     *
     * @ORM\Column(name="rating", type="integer")
     */
    private $rating;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reviews")
     */
    private $user;

    /**
     * @var Book
     *
     * @ORM\ManyToOne(targetEntity="Book", inversedBy="reviews")
     */
    private $bookReviewed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="edited_date", type="datetime", nullable=true)
     */
    private $edited;

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
     * Set reviewComments
     *
     * @param string $reviewComments
     *
     * @return Review
     */
    public function setReviewComments(string $reviewComments)
    {
        $this->reviewComments = $reviewComments;

        return $this;
    }

    /**
     * Get reviewComments
     *
     * @return string
     */
    public function getReviewComments()
    {
        return $this->reviewComments;
    }

    /**
     * Set rating
     *
     * @param int $rating
     *
     * @return Review
     */
    public function setRating(int $rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Review
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set bookReviewed
     *
     * @param Book $bookReviewed
     *
     * @return Review
     */
    public function setBookReviewed(Book $bookReviewed = null)
    {
        $this->bookReviewed = $bookReviewed;

        return $this;
    }

    /**
     * Get bookReviewed
     *
     * @return Book
     */
    public function getBookReviewed()
    {
        return $this->bookReviewed;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;
    }


    /**
     * Set title
     *
     * @param string $reviewTitle
     *
     * @return Review
     */
    public function setReviewTitle($reviewTitle)
    {
        $this->reviewTitle = $reviewTitle;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getReviewTitle()
    {
        return $this->reviewTitle;
    }

    /**
     * Set edited
     *
     * @param \DateTime $edited
     *
     * @return Review
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * Get edited
     *
     * @return \DateTime
     */
    public function getEdited()
    {
        return $this->edited;
    }
}
