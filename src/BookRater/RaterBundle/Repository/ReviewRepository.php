<?php

namespace BookRater\RaterBundle\Repository;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Entity\User;
use \Doctrine\ORM\EntityRepository;

class ReviewRepository extends EntityRepository
{

    public function findAllQueryBuilder($filter = null)
    {
        $qb = $this->createQueryBuilder('review');

        if ($filter) {
            $qb->innerJoin('review.user', 'review_user')
                ->innerJoin('review.book', 'review_book')
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
        return $qb;
    }

    public function getLatestByFilter($filter = null)
    {
        return $this->findAllQueryBuilder($filter)
            ->addOrderBy('review.created', 'DESC')
            ->getQuery();
    }

    /**
     * @param Book $book
     * @return mixed
     */
    public function findAllByBook(Book $book)
    {
        return $this->createQueryBuilderForBook($book)
            ->getQuery()
            ->execute();
    }

    public function createQueryBuilderForBook(Book $book)
    {
        $qb = $this->createQueryBuilder('review');
        return $qb
            ->andWhere(
                $qb->expr()->eq('review.book', ':book')
            )
            ->setParameter('book', $book->getId());
    }

    public function createQueryBuilderForUser(User $user)
    {
        $qb = $this->createQueryBuilder('review');
        return $qb
            ->andWhere(
                $qb->expr()->eq('review.user', ':user')
            )
            ->setParameter('user', $user->getId());
    }

}
