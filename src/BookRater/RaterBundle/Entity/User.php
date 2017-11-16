<?php
// src/BookRater/RaterBundle/Entity/User.php

namespace BookRater\RaterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
final class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    protected $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="user")
     */
    private $reviews;

    public function __construct()
    {
        parent::__construct();
        $this->reviews = new ArrayCollection();
    }

    public function getName() {
        return $this->name;
    }

    /**
     * Add review
     *
     * @param \BookRater\RaterBundle\Entity\Review $review
     *
     * @return User
     */
    public function addReview(\BookRater\RaterBundle\Entity\Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param \BookRater\RaterBundle\Entity\Review $review
     */
    public function removeReview(\BookRater\RaterBundle\Entity\Review $review)
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
}
