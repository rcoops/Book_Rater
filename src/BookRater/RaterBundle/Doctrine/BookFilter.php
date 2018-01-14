<?php

namespace BookRater\RaterBundle\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class BookFilter extends SQLFilter
{

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->getReflectionClass()->name != 'BookRater\RaterBundle\Entity\Book') {
            return "";
        }
        $filter = str_replace("'", '', $this->getParameter('filter'));
        $sql = sprintf('%s.title LIKE \'%%%s%%\'', $targetTableAlias, $filter);

        return $sql;
    }

}