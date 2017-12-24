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
use App\Entity\Person;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPersonData extends BaseFixture
{
    /**
     * create an instance with all random values.
     *
     * @return Person
     */
    protected function getAllRandomInstance()
    {
        $person = new Person();
        $this->fillRandomAddress($person);
        $this->fillRandomCommunication($person);
        $this->fillRandomPerson($person);

        return $person;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $person = $this->getAllRandomInstance();
        $person->setEmail('info@nodika.ch');
        $manager->persist($person);
        $manager->flush();

        $this->setReference('person-1', $person);

        $person = $this->getAllRandomInstance();
        $person->setEmail('markus@praxis.ch');
        $manager->persist($person);
        $manager->flush();

        $this->setReference('person-2', $person);

        $person = $this->getAllRandomInstance();
        $person->setEmail('daniel@praxis.ch');
        $manager->persist($person);
        $manager->flush();

        $this->setReference('person-3', $person);
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 11;
    }
}
