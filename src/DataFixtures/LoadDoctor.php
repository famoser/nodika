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
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        //load some doctors
        $this->loadSomeRandoms($manager, 30);

        //create admin
        $admin = $this->getRandomInstance();
        $admin->setEmail('info@nodika.ch');
        $admin->setPlainPassword('asdf');
        $admin->setPassword();
        $admin->setIsAdministrator(true);
        $admin->setReceivesAdministratorMail(true);
        $manager->persist($admin);

        //create doctor which is invited
        $invitedUser = $this->getRandomInstance();
        $invitedUser->invite();
        $manager->persist($invitedUser);

        //create doctor which is not invited yet
        $notInvitedUser = $this->getRandomInstance();
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

    /**
     * create an instance with all random values.
     *
     * @param bool $acceptInvitation
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
