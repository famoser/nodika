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
use App\Entity\Organisation;
use App\Entity\Person;
use Doctrine\Common\Persistence\ObjectManager;

class LoadOrganisationData extends BaseFixture
{
    /**
     * create an instance with all random values.
     *
     * @return Organisation
     */
    protected function getAllRandomInstance()
    {
        $organisation = new Organisation();
        $this->fillRandomCommunication($organisation);
        $this->fillRandomAddress($organisation);
        $this->fillRandomThing($organisation);

        return $organisation;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $organisation = $this->getAllRandomInstance();
        $organisation->setName('knbu.ch');
        /* @var Person $person */
        $person = $this->getReference('person-1');
        $organisation->addLeader($person);

        $organisation->setIsActive(true);
        $organisation->setActiveEnd(new \DateTime('now + 1 month'));
        $manager->persist($organisation);
        $manager->flush();

        $this->setReference('organisation-1', $organisation);
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 12;
    }
}
