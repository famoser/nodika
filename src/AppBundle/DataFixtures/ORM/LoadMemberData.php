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
        $organisation = $this->getAllRandomInstance();
        $organisation->setOrganisation($this->getReference("organisation-1"));
        $organisation->addPerson($this->getReference("person-1"));
        $manager->persist($organisation);
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