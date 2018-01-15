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
class User extends BaseUser
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
     * @ORM\OneToMany(targetEntity="Review", mappedBy="user", cascade={"remove"})
     */
    private $reviews;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Message", mappedBy="user", cascade={"remove"})
     */
    private $messages;

    public function __construct()
    {
        parent::__construct();
        $this->reviews = new ArrayCollection();
        $this->messages = new ArrayCollection();
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
    public function addReview(Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param \BookRater\RaterBundle\Entity\Review $review
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
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Message $message
     *
     * @return User
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param \BookRater\RaterBundle\Entity\Message $message
     */
    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
    }

    public function __toString()
    {
        return $this->username;
    }
}
