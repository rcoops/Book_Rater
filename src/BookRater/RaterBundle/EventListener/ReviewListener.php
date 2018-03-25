<?php

namespace BookRater\RaterBundle\EventListener;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PostRemove;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreRemove;
use Doctrine\ORM\Mapping\PreUpdate;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ReviewListener
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ReviewListener constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Review $review
     * @param LifecycleEventArgs $event
     *
     * @PostPersist
     */
    public function postPersistHandler(Review $review, LifecycleEventArgs $event)
    {
        $this->updateBookRating($review->getBook());
    }

    /**
     * @param Review $review
     * @param LifecycleEventArgs $event
     *
     * @PostUpdate
     */
    public function postUpdateHandler(Review $review, LifecycleEventArgs $event)
    {
        $this->updateBookRating($review->getBook());
    }

    /**
     * @param Review $review
     * @param LifecycleEventArgs $event
     *
     * @PostRemove
     */
    public function postRemoveHandler(Review $review, LifecycleEventArgs $event)
    {
        $this->updateBookRating($review->getBook());
    }

    private function updateBookRating(Book $book)
    {
        $reviewRatings = $book->getReviews()
            ->map(function (Review $review) {
                return $review->getRating();
            })
            ->toArray();
        $averageRating = $reviewRatings ? array_sum($reviewRatings) / count($reviewRatings) : null;

        $book->setAverageRating($averageRating);

        $this->em->persist($book);
        $this->em->flush();
    }

}