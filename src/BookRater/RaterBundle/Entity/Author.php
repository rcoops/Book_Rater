<?php

namespace BookRater\RaterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

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
 * @ORM\Table(name="authors")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\AuthorRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Author
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     *
     * @Serializer\Expose
     *
     * @SWG\Property(description="The unique identifier of the book.")
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
     * @ORM\Column(type="string", length=255)
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
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     */
    private $initial;

    /**
     * @var Collection
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
    public function getId() : int
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
    public function setFirstName(string $firstName) : Author
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string|null
     */
    public function getFirstName() : ?string
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
    public function setLastName(string $lastName) : Author
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string|null
     */
    public function getLastName() : ?string
    {
        return $this->lastName;
    }

    /**
     * Set initial
     *
     * @param string|null $initial
     *
     * @return Author
     */
    public function setInitial(?string $initial) : Author
    {
        $this->initial = $initial;

        return $this;
    }

    /**
     * Get initial
     *
     * @return string|null
     */
    public function getInitial() : ?string
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
    public function addBooksAuthored(Book $bookAuthored) : Author
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
     *
     * @return Author
     */
    public function removeBooksAuthored(Book $bookAuthored) : Author
    {
        if ($this->booksAuthored->contains($bookAuthored)) {
            $bookAuthored->getAuthors()->removeElement($this);
        }

        $this->booksAuthored->removeElement($bookAuthored);

        return $this;
    }

    /**
     * @return Collection|Book[]
     */
    public function getBooksAuthored() : Collection
    {
        return $this->booksAuthored;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        $name = $this->getLastName() . ', ' . $this->getFirstName();
        if ($this->initial) {
            return $name . ' ' . $this->initial;
        }
        return $name;
    }

}
