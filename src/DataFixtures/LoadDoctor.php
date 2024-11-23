<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Factories\DoctorFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDoctor extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        // load some doctors
        $factory = new DoctorFactory();
        $factory->many(30);

        // create doctor which is invited
        $invitedUser = $factory->create();
        $invitedUser->invite();
        $manager->persist($invitedUser);

        // create doctor which is not invited yet
        $notInvitedUser = $factory->create();
        $manager->persist($notInvitedUser);

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return static::ORDER;
    }
}
