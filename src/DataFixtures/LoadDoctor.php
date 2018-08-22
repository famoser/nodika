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

use App\DataFixtures\Base\BaseFixture;
use App\Entity\Doctor;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDoctor extends BaseFixture
{
    const ORDER = 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadSomeRandoms($manager, 30);
        $manager->flush();

        $user = $this->getRandomInstance();
        $user->setEmail("info@nodika.ch");
        $user->setPlainPassword("asdf1234");
        $user->setPassword();
        $user->setIsAdministrator(true);

        $manager->persist($user);
        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER;
    }

    /**
     * create an instance with all random values.
     *
     * @return Doctor
     */
    protected function getRandomInstance()
    {
        $doctor = new Doctor();
        $this->fillAddress($doctor);
        $this->fillCommunication($doctor);
        $this->fillPerson($doctor);
        $this->fillUser($doctor);

        return $doctor;
    }
}
