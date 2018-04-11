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
use App\Entity\FrontendUser;
use Doctrine\Common\Persistence\ObjectManager;

class LoadFrontendUser extends BaseFixture
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
        $user->setEmail("info@nodkia.ch");
        $user->setPlainPassword("asdf1234");
        $user->setPassword();

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
     * @return FrontendUser
     */
    protected function getRandomInstance()
    {
        $frontendUser = new FrontendUser();
        $this->fillAddress($frontendUser);
        $this->fillCommunication($frontendUser);
        $this->fillPerson($frontendUser);
        $this->fillUser($frontendUser);

        return $frontendUser;
    }
}
