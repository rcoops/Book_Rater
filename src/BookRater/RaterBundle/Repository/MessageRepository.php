<?php

namespace BookRater\RaterBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{

    public function findAll()
    {
        $this->createQueryBuilder('message')
            ->addOrderBy('message.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

}