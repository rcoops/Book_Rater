<?php

namespace BookRater\RaterBundle\Repository;

use BookRater\RaterBundle\Entity\ApiToken;
use BookRater\RaterBundle\Entity\User;
use \Doctrine\ORM\EntityRepository;

class ApiTokenRepository extends EntityRepository
{
    /**
     * @param $token
     * @return ApiToken|object
     */
    public function findOneByToken($token)
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * @param User $user
     * @return ApiToken[]
     */
    public function findAllForUser(User $user)
    {
        return $this->findBy(['user' => $user->getId()]);
    }

}
