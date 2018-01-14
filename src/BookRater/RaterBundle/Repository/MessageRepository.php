<?php

namespace BookRater\RaterBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{

    public function findUnreadMessages()
    {
        $qb = $this->createQueryBuilder('message');
        return $qb
            ->andWhere($qb->expr()->eq('message.isRead', ':isRead'))
            ->addOrderBy('message.created', 'DESC')
            ->setParameter('isRead', false)
            ->getQuery()
            ->getResult();
    }

}