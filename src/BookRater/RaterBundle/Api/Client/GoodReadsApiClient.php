<?php

namespace BookRater\RaterBundle\Api\Client;

use BookRater\RaterBundle\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;

class GoodReadsApiClient
{

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
     * @param GuzzleClient $guzzleClient
     * @param string $goodBooksDeveloperKey
     */
    public function __construct(GuzzleClient $guzzleClient, string $goodBooksDeveloperKey)
    {
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

    /**
     * @param Book $book
     * @throws GuzzleException
     */
    public function updateGoodReadsInfo(Book $book)
    {
        $isbn = $book->getIsbn();

        if ($isbn) {
            $query = sprintf('review_counts.json?key=%s&isbns=%s', $this->goodBooksDeveloperKey, $isbn);
            try {
                $response = $this->guzzleClient->request('GET', $query);
                if ($response && $response->getBody()) {
                    $data = json_decode($response->getBody());
                    if (isset($data->books) && !empty($data->books)) {
                        $reviewInfo = $data->books[0];
                        $rating = round($reviewInfo->average_rating);
                        $book->setGoodReadsRating($rating);
                        $goodReadsId = round($reviewInfo->id);
                        $book->setGoodReadsId($goodReadsId);
                    }
                }
            } catch (GuzzleException $e) {
                if ($e->getCode() !== Response::HTTP_NOT_FOUND) {
                    throw $e; // Not found is just fine as there's no guarantee it will be on the site
                }
            }
        }
    }

}
