<?php

namespace BookRater\RaterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Author
 *
 * @ORM\Table(name="author")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\AuthorRepository")
 */
final class Author
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
     * @ORM\Column(name="firstName", type="string", length=255)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="initial", type="string", length=255, nullable=true)
     */
    private $initial;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Book", inversedBy="authors")
     * @ORM\JoinTable(name="author_books")
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
     * @param Book $booksAuthored
     *
     * @return Author
     */
    public function addBooksAuthored(Book $booksAuthored)
    {
        $this->booksAuthored[] = $booksAuthored;

        return $this;
    }

    /**
     * Remove booksAuthored
     *
     * @param Book $booksAuthored
     */
    public function removeBooksAuthored(Book $booksAuthored)
    {
        $this->booksAuthored->removeElement($booksAuthored);
    }

    /**
     * Get booksAuthored
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBooksAuthored()
    {
        return $this->booksAuthored;
    }

    public function getDisplayName()
    {
        $name = $this->getLastName() . ', ' . $this->getFirstName();
        if ($this->initial)
        {
            return $name . ' ' . $this->initial;
        }
        return $name;
    }

}
