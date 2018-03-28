<?php

namespace BookRater\RaterBundle\Api\Doc\Model;

use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class ReviewModel
 * @package BookRater\RaterBundle\Api\Doc\Model
 * @Serializer\ExclusionPolicy("all")
 */
class ReviewModel
{

    /**
     * @var string
     * @SWG\Parameter(name="title", type="string")
     *
     * @Serializer\Expose
     */
    private $title;

    /**
     * @var string
     * @SWG\Parameter(name="comments", type="string")
     *
     * @Serializer\Expose
     */
    private $comments;

    /**
     * @var int
     * @SWG\Parameter(name="rating", type="integer")
     *
     * @Serializer\Expose
     */
    private $rating;

    /**
     * @var int
     * @SWG\Parameter(name="bookId", type="integer")
     *
     * @Serializer\Expose
     */
    private $bookId;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments(string $comments): void
    {
        $this->comments = $comments;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    /**
     * @return int
     */
    public function getBookId(): int
    {
        return $this->bookId;
    }

    /**
     * @param int $bookId
     */
    public function setBookId(int $bookId): void
    {
        $this->bookId = $bookId;
    }

}
