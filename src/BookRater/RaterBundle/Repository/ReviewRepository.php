<?php

namespace BookRater\RaterBundle\Repository;

use BookRater\RaterBundle\Entity\Book;
use \Doctrine\ORM\EntityRepository;

class ReviewRepository extends EntityRepository
{

    public function getLatestByFilter($filter = null)
    {
        $qb = $this->createQueryBuilder('review')
            ->addOrderBy('review.created', 'DESC');

        if ($filter) {
            $qb->innerJoin('review.user', 'review_user')
                ->innerJoin('review.bookReviewed', 'review_book')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('review_book.title', ':filter'),
                        $qb->expr()->like('review_user.username', ':filter'),
                        $qb->expr()->eq('review.rating', ':num')
                    )
                );
            $qb->setParameter('filter', '%' . $filter . '%');
            $qb->setParameter('num', $filter);
        }
        return $qb->getQuery();
    }

    /**
     * @param Book $book
     * @return mixed
     */
    public function findAllByBook(Book $book)
    {
        $qb = $this->createQueryBuilder('review');

        return $qb
            ->andWhere(
                $qb->expr()->eq('review.bookReviewed', ':book')
            )
            ->setParameter('book', $book->getId())
            ->getQuery()
            ->execute();
    }

}
