<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2017
 * Time: 15:48
 */

namespace AppBundle\DataFixtures\ORM\Production;


use AppBundle\DataFixtures\ORM\Base\BaseFixture;
use AppBundle\Entity\Member;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMemberData extends BaseFixture
{

    /**
     * create an instance with all random values
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
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $member = $this->getAllRandomInstance();
        $member->setOrganisation($this->getReference("organisation-1"));
        $member->addPerson($this->getReference("person-1"));
        $manager->persist($member);

        $member = $this->getAllRandomInstance();
        $member->setOrganisation($this->getReference("organisation-1"));
        $member->addPerson($this->getReference("person-2"));
        $manager->persist($member);

        $member = $this->getAllRandomInstance();
        $member->setOrganisation($this->getReference("organisation-1"));
        $member->addPerson($this->getReference("person-3"));
        $manager->persist($member);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 14;
    }
}