<?php

namespace BookRater\FixturesBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

class LoadFixtures implements ORMFixtureInterface
{

    public function load(ObjectManager $objectManager)
    {
        Fixtures::load(__DIR__ . '/fixtures.yml', $objectManager);
    }

}