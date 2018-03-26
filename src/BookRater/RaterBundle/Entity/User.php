<?php
// src/BookRater/RaterBundle/Entity/User.php

namespace BookRater\RaterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\UserRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"admin"})
     * @Serializer\Expose
     *
     * @SWG\Property(description="The unique identifier of the user.")
     */
    protected $id;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="user", cascade={"remove"})
     *
     * @Serializer\Expose
     * @Serializer\Groups({"users", "books", "authors", "messages", "reviews"})
     *
     * @SWG\Property(description="A list of all reviews created by the user.")
     */
    private $reviews;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Message", mappedBy="user", cascade={"remove"})
     *
     * @Serializer\Expose
     * @Serializer\Groups({"admin"})
     *
     * @SWG\Property(description="A list of all messages created by he user.")
     */
    private $messages;

    public function __construct()
    {
        parent::__construct();

        $this->reviews = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Add review
     *
     * @param Review $review
     *
     * @return User
     */
    public function addReview(Review $review) : User
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param Review $review
     *
     * @return User
     */
    public function removeReview(Review $review) : User
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
     * @return Collection|Message[]
     */
    public function getMessages() : Collection
    {
        return $this->messages;
    }

    /**
     * @param Message $message
     *
     * @return User
     */
    public function addMessage(Message $message) : User
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     *
     * @param Message $message
     *
     * @return User
     */
    public function removeMessage(Message $message) : User
    {
        $this->messages->removeElement($message);

        return $this;
    }

    /**
     * @return string|null
     */
    public function __toString() : ?string
    {
        return $this->username;
    }

}
