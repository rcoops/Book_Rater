<?php

namespace BookRater\RaterBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\MessageRepository")
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
     * @ORM\ManyToOne(targetEntity="BookRater\RaterBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
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
    public function getisRead()
    {
        return $this->isRead;
    }

    /**
     * @param mixed $isRead
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead === null ? false : true;
    }

}