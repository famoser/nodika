<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Production;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\LoadDoctor;
use App\Entity\Doctor;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAdmin extends LoadDoctor
{
    const ORDER = 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        //create admin
        $admin = $this->getRandomInstance();
        $admin->setEmail('info@nodika.ch');
        $admin->setPlainPassword('asdf');
        $admin->setPassword();
        $admin->setIsAdministrator(true);
        $admin->setReceivesAdministratorMail(true);
        $manager->persist($admin);

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
