<?php

namespace BookRater\RaterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Author
 *
 * @Hateoas\Relation(
 *     "self",
 *     href=@Hateoas\Route(
 *       "api_authors_show",
 *       parameters = { "lastName" = "expr(object.getLastName())",
 *         "firstName" = "expr(object.getFirstName())" }
 *     )
 * )
 * @Hateoas\Relation(
 *     "books",
 *     href=@Hateoas\Route(
 *         "api_authors_books_list",
 *         parameters = { "lastName" = "expr(object.getLastName())",
 *         "firstName" = "expr(object.getFirstName())" }
 *     )
 * )
 *
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\AuthorRepository")
 * @ORM\Table(name="author")
 *
 * @Serializer\ExclusionPolicy("all")
 */
final class Author
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id
     *
     * @Serializer\Expose
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="First name must not be blank.")
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z]+$/",
     *     match=true,
     *     message="First name must consist of letters only."
     * )
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $firstName;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Last name must not be blank.")
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z]+$/",
     *     match=true,
     *     message="Last name must consist of letters only."
     * )
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="initial", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     */
    private $initial;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\JoinTable(name="author_books")
     * @ORM\ManyToMany(targetEntity="Book", inversedBy="authors")
     *
     * @Serializer\Groups({"authors"})
     * @Serializer\Expose
     */
    private $booksAuthored;

    public function __construct()
    {
        $this->booksAuthored = new ArrayCollection();
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Author
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Author
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set initial
     *
     * @param string $initial
     *
     * @return Author
     */
    public function setInitial(string $initial)
    {
        $this->initial = $initial;

        return $this;
    }

    /**
     * Get initial
     *
     * @return string
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * Add booksAuthored
     *
     * @param Book $bookAuthored
     *
     * @return Author
     */
    public function addBookAuthored(Book $bookAuthored)
    {
        if (!$this->booksAuthored->contains($bookAuthored)) {
            $this->booksAuthored[] = $bookAuthored;
        }

        return $this;
    }

    /**
     * Remove booksAuthored
     *
     * @param Book $bookAuthored
     */
    public function removeBooksAuthored(Book $bookAuthored)
    {
        if ($this->booksAuthored->contains($bookAuthored)) {
            $bookAuthored->getAuthors()->removeElement($this);
        }

        $this->booksAuthored->removeElement($bookAuthored);
    }

    /**
     * @return ArrayCollection|Book[]
     */
    public function getBooksAuthored()
    {
        return $this->booksAuthored;
    }

    public function getDisplayName()
    {
        $name = $this->getLastName() . ', ' . $this->getFirstName();
        if ($this->initial) {
            return $name . ' ' . $this->initial;
        }
        return $name;
    }

    /**
     * Add booksAuthored
     *
     * @param \BookRater\RaterBundle\Entity\Book $booksAuthored
     *
     * @return Author
     */
    public function addBooksAuthored(Book $booksAuthored)
    {
        $this->booksAuthored[] = $booksAuthored;

        return $this;
    }

    public function __toString()
    {
        $name = $this->getLastName() . ', ' . $this->getFirstName();
        if ($this->initial) {
            return $name . ' ' . $this->initial;
        }
        return $name;
    }

}
