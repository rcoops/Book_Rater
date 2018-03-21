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

}
