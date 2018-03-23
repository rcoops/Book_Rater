<?php

namespace BookRater\RaterBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\MessageRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Message
{

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     *
     * @Assert\NotNull(message="User must be included.")
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="messages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var null|string
     *
     * @ORM\Column(type="string")
     */
    private $subject;

    /**
     * @var null|string
     *
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @var null|DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default": false})
     */
    private $isRead;

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
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Message
     */
    public function setUser(User $user) : Message
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param User $user
     *
     * @return Message
     */
    public function removeUser(User $user) : Message
    {
        $user->removeMessage($this);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubject() : ?string
    {
        return $this->subject;
    }

    /**
     * @param null|string $subject
     *
     * @return Message
     */
    public function setSubject(?string $subject) : Message
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMessage() : ?string
    {
        return $this->message;
    }

    /**
     * @param null|string $message
     *
     * @return Message
     */
    public function setMessage(?string $message) : Message
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param null|DateTime $created
     *
     * @return Message
     */
    public function setCreated(?DateTime $created) : Message
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function getIsRead() : ?bool
    {
        return $this->isRead;
    }

    /**
     * @param null|bool $isRead
     *
     * @return Message
     */
    public function setIsRead(?bool $isRead = false) : Message
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->subject;
    }

}