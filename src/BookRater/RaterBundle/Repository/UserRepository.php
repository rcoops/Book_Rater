<?php

namespace BookRater\RaterBundle\Repository;

use BookRater\RaterBundle\Entity\User;
use \Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

    /**
     * @param $username
     * @return User|object
     */
    public function findUserByUsername($username)
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * @return User
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAny()
    {
        return $this->createQueryBuilder('u')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
