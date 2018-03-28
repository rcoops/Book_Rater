<?php

namespace BookRater\RaterBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *   "self",
 *   href=@Hateoas\Route(
 *     "api_reviews_show",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "user",
 *   href=@Hateoas\Route(
 *     "api_users_show",
 *     parameters = { "identifier" = "expr(object.getId())" }
 *   ),
 *   exclusion=@Hateoas\Exclusion(groups={"admin"})
 * )
 * @ORM\Table(name="messages")
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
     *
     * @Serializer\Expose
     * @Serializer\Groups({"messages", "books", "authors", "reviews", "admin"})
     *
     * @SWG\Property(description="The unique identifier of the message.")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="messages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     * @SWG\Property(description="The creator of the message.")
     */
    private $user;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The message must have a subject.")
     *
     * @ORM\Column(type="string")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"messages", "books", "authors", "reviews", "admin"})
     *
     * @SWG\Property(description="A brief description of the message content.")
     */
    private $subject;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The message must have content.")
     *
     * @ORM\Column(type="text")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"messages", "books", "authors", "reviews", "admin"})
     *
     * @SWG\Property(description="The message's main content.")
     */
    private $message;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_date", type="datetime")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"messages", "books", "authors", "reviews", "admin"})
     *
     * @SWG\Property(description="The date and time on which the review was created.")
     */
    private $created;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Serializer\Expose
     * @Serializer\Groups({"messages", "books", "authors", "reviews", "admin"})
     *
     * @SWG\Property(description="Whether or not the message has been marked as read by admin")
     */
    private $isRead = false;

    /**
     * @Serializer\SerializedName("_links")
     * @Serializer\Expose
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin"})
     *
     * @SWG\Property(
     *   type="object",
     *   description="A series of resource urls conforming to application/hal+json standards",
     *   @SWG\Property(type="string", property="self", description="A relative url to this resource."),
     *   @SWG\Property(
     *     type="string",
     *     property="user",
     *     description="A relative url to the user associated with this resource.",
     *   ),
     * )
     */
    // This is a fake property and will be overridden dynamically during serialisation - here for swagger's benefit
    private $links;

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
     * @return string|null
     */
    public function getSubject() : ?string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     *
     * @return Message
     */
    public function setSubject(?string $subject) : Message
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage() : ?string
    {
        return $this->message;
    }

    /**
     * @param string|null $message
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
     * @param DateTime|null $created
     *
     * @return Message
     */
    public function setCreated(?DateTime $created) : Message
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsRead() : ?bool
    {
        return $this->isRead;
    }

    /**
     * @param bool|null $isRead
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
     *
     * @Serializer\Expose
     * @Serializer\Groups({"messages", "admin"})
     * @Serializer\VirtualProperty(name="user")
     * @Serializer\SerializedName("user")
     */
    public function getUsername()
    {
        return $this->user->getUsername();
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->subject;
    }

}