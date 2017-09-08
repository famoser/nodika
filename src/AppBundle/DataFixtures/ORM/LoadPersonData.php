<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 10:09
 */

namespace AppBundle\DataFixtures\ORM\Production;


use AppBundle\DataFixtures\ORM\Base\BaseFixture;
use AppBundle\Entity\Person;
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
        $person->setEmail("info@nodika.ch");
        $manager->persist($person);
        $manager->flush();

        $this->setReference("person-1", $person);
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 11;
    }
}