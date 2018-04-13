<?php

namespace BookRater\RaterBundle\Api\Client;

use BookRater\RaterBundle\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client as GuzzleClient;

class GoodReadsApiClient
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var GuzzleClient
     */
    private $guzzleClient;

    /**
     * @var string
     */
    private $goodBooksDeveloperKey;


    /**
     * GoodReadsApiClient constructor.
     * @param EntityManagerInterface $em
     * @param GuzzleClient $guzzleClient
     * @param string $goodBooksDeveloperKey
     */
    public function __construct(EntityManagerInterface $em, GuzzleClient $guzzleClient, string $goodBooksDeveloperKey)
    {
        $this->em = $em;
        $this->guzzleClient = $guzzleClient;
        $this->goodBooksDeveloperKey = $goodBooksDeveloperKey;
    }

    public function getReviewsWidget($goodReadsId)
    {
        $query = sprintf('show.json?key=%s&id=%s', $this->goodBooksDeveloperKey, $goodReadsId);
        $response = $this->guzzleClient->request('GET', $query);
        $data = json_decode($response->getBody());
        return $data->reviews_widget;
    }

    public function updateGoodReadsInfo(Book $book)
    {
        $isbn = $book->getIsbn();

        if ($isbn) {
            $query = sprintf('review_counts.json?key=%s&isbns=%s', $this->goodBooksDeveloperKey, $isbn);
            $response = $this->guzzleClient->request('GET', $query);
            if ($response && $response->getBody()) {
                $data = json_decode($response->getBody());
                if (isset($data['books']) && !empty($data['books'])) {
                    $rating = round($data['books'][0]->average_rating);
                    $book->setGoodReadsRating($rating);
                    $goodReadsId = round($data['books'][0]->id);
                    $book->setGoodReadsId($goodReadsId);
                }
            }
        }
    }

}
