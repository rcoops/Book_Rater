<?php

namespace BookRater\FixturesBundle\DataFixtures\ORM;

use BookRater\RaterBundle\Entity\Review;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

class LoadFixtures implements ORMFixtureInterface
{

    public function load(ObjectManager $objectManager)
    {
        Fixtures::load(__DIR__ . '/fixtures.yml', $objectManager);
        $reviewRepository = $objectManager->getRepository('BookRaterRaterBundle:Review');
        $books = $objectManager->getRepository('BookRaterRaterBundle:Book')
            ->findAll();
        foreach ($books as $book) {
            $reviews = $reviewRepository->findAllByBook($book);
            $reviewRatings = array_map(function (Review $review) {
                return $review->getRating();
            }, $reviews);
            $averageRating = $reviewRatings ? array_sum($reviewRatings) / count($reviewRatings) : null;
            $book->setAverageRating($averageRating);

            $objectManager->persist($book);
        }
        $objectManager->flush();
    }

}
