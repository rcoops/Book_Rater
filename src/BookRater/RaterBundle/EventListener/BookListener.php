<?php

namespace BookRater\RaterBundle\EventListener;

use BookRater\RaterBundle\Api\Client\GoodReadsApiClient;
use BookRater\RaterBundle\Api\Client\GoogleBooksApiClient;
use BookRater\RaterBundle\Entity\Book;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

class BookListener
{

    /**
     * @var GoodReadsApiClient
     */
    private $goodReadsApiClient;
    /**
     * @var GoogleBooksApiClient
     */
    private $googleBooksApiClient;

    /**
     * ReviewListener constructor.
     * @param GoogleBooksApiClient $googleBooksApiClient
     * @param GoodReadsApiClient $goodReadsApiClient
     */
    public function __construct(GoogleBooksApiClient $googleBooksApiClient, GoodReadsApiClient $goodReadsApiClient)
    {
        $this->googleBooksApiClient = $googleBooksApiClient;
        $this->goodReadsApiClient = $goodReadsApiClient;
    }

    /**
     * @param Book $book
     * @param LifecycleEventArgs $event
     *
     * @PrePersist
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function prePersistHandler(Book $book, LifecycleEventArgs $event)
    {
        $this->googleBooksApiClient->updateGoogleBooksInfo($book);
        $this->goodReadsApiClient->updateGoodReadsInfo($book);
    }

    /**
     * @param Book $book
     * @param LifecycleEventArgs $event
     *
     * @PreUpdate
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function preUpdateHandler(Book $book, LifecycleEventArgs $event)
    {
        $this->googleBooksApiClient->updateGoogleBooksRating($book);
        $this->goodReadsApiClient->updateGoodReadsInfo($book);
    }

}
