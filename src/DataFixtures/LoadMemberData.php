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
use App\Entity\Member;
use App\Entity\Person;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMemberData extends BaseFixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $member = $this->getAllRandomInstance();
        $member->setOrganisation($this->getReference('organisation-1'));
        $member->setName('Kleintierpraxis baselwest');
        /* @var Person $person */
        $person = $this->getReference('person-1');
        $member->addPerson($person);
        $person->addMember($member);
        $manager->persist($member);
        $manager->persist($person);
        $this->setReference('member-1', $member);

        $member = $this->getAllRandomInstance();
        $member->setOrganisation($this->getReference('organisation-1'));
        $member->setName('Kleintierpraxis am Ring');
        /* @var Person $person */
        $person = $this->getReference('person-2');
        $member->addPerson($person);
        $person->addMember($member);
        $manager->persist($member);
        $manager->persist($person);
        $this->setReference('member-2', $member);

        $member = $this->getAllRandomInstance();
        $member->setOrganisation($this->getReference('organisation-1'));
        $member->setName('VET 4 PET');
        /* @var Person $person */
        $person = $this->getReference('person-3');
        $member->addPerson($person);
        $person->addMember($member);
        $manager->persist($member);
        $manager->persist($person);
        $this->setReference('member-3', $member);

        $manager->flush();
    }

    /**
     * create an instance with all random values.
     *
     * @return Member
     */
    protected function getAllRandomInstance()
    {
        $member = new Member();
        $this->fillRandomCommunication($member);
        $this->fillRandomAddress($member);
        $this->fillRandomThing($member);

        return $member;
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 14;
    }
}
