<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2017
 * Time: 15:48
 */

namespace AppBundle\DataFixtures\ORM\Production;


use AppBundle\DataFixtures\ORM\Base\BaseFixture;
use AppBundle\Entity\EventLine;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventLineData extends BaseFixture
{

    /**
     * create an instance with all random values
     *
     * @return EventLine
     */
    protected function getAllRandomInstance()
    {
        $eventLine = new EventLine();
        $this->fillRandomThing($eventLine);
        return $eventLine;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $eventLine = $this->getAllRandomInstance();
        $eventLine->setOrganisation($this->getReference("organisation-1"));
        $eventLine->setName("Notfalldienst");
        $manager->persist($eventLine);

        $eventLine = $this->getAllRandomInstance();
        $eventLine->setOrganisation($this->getReference("organisation-1"));
        $eventLine->setName("Wochentelefon");
        $manager->persist($eventLine);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 15;
    }
}