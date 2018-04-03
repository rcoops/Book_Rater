<?php

namespace BookRater\RaterBundle\Repository;

use BookRater\RaterBundle\Entity\User;
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

    public function createQueryBuilderForUser(User $user)
    {
        $qb = $this->createQueryBuilder('message');
        return $qb
            ->andWhere(
                $qb->expr()->eq('message.user', ':user')
            )
            ->setParameter('user', $user->getId());
    }

}
