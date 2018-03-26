<?php

namespace BookRater\RaterBundle\Security\Voter;

use BookRater\RaterBundle\Entity\Message;
use BookRater\RaterBundle\Entity\Review;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\Role;

class OwnerVoter extends Voter
{

    protected function supports($attribute, $subject)
    {
        // you only want to vote if the attribute and subject are what you expect
        return $attribute === 'OWNER' && ($subject instanceof Review || $subject instanceof Message);
    }

    /**
     * @param string $attribute
     * @param Review|Message $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $roleNames = array_map(function (Role $role) {
            return $role->getRole();
        }, $token->getRoles());

        return in_array('ROLE_ADMIN', $roleNames)
            || $subject->getUser()->getUsername() === $token->getUsername();
    }

}
