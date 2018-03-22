<?php

namespace BookRater\RaterBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\MessageRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Message
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *
     * @Assert\NotNull(message="User must be included.")
     *
     * @ORM\ManyToOne(targetEntity="BookRater\RaterBundle\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": false})
     */
    private $isRead;

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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }


    /**
     * Remove user
     *
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $user->removeMessage($this);
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
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
     * @return mixed
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * @param mixed $isRead
     */
    public function setIsRead($isRead = false)
    {
        $this->isRead = !$isRead ? false : true; // required to deal with null passed by fixtures
    }

    public function __toString()
    {
        return $this->subject;
    }

}