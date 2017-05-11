<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 10:09
 */

namespace AppBundle\DataFixtures\ORM\Production;


use AppBundle\DataFixtures\ORM\Base\BaseFixture;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Person;
use AppBundle\Entity\Traits\PersonTrait;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPersonData extends BaseFixture
{
    /**
     * create an instance with all random values
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
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $person = $this->getAllRandomInstance();
        /* @var FrontendUser $user */
        $user = $this->getReference("user-1");
        $user->setPerson($person);
        $manager->persist($user);
        $manager->persist($person);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}