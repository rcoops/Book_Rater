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
        return $this->findAllQueryBuilder()
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $identifier
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByIdentifier($identifier)
    {
        if (!$identifier) {
            return null;
        }
        $qb = $this->findAllQueryBuilder();
        return $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('user.id', ':identifier'),
                    $qb->expr()->eq('user.username', ':identifier'),
                    $qb->expr()->eq('user.emailCanonical', ':identifier')
                )
            )
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('user')
            ->addOrderBy('user.id');
    }

}
