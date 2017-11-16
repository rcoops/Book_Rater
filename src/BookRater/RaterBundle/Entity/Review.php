<?php

namespace BookRater\RaterBundle\Entity;

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
     * Get id
     *
     * @return int
     */
    public function getId()
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
    public function getUser()
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

}
