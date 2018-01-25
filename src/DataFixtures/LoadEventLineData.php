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
use App\Entity\EventLine;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventLineData extends BaseFixture
{
    /**
     * create an instance with all random values.
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
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $eventLine = $this->getAllRandomInstance();
        $eventLine->setOrganisation($this->getReference('organisation-1'));
        $eventLine->setName('Notfalldienst');
        $eventLine->setDescription('Sie kümmern sich um die Notfälle und nehmen die Anrufe der Notfalldienstnummer entgegen');
        $manager->persist($eventLine);
        $this->setReference('event-line-1', $eventLine);

        $eventLine = $this->getAllRandomInstance();
        $eventLine->setOrganisation($this->getReference('organisation-1'));
        $eventLine->setName('Wochentelefon');
        $eventLine->setDescription('Sie kümmern sich um das Wochentelefon');
        $manager->persist($eventLine);
        $this->setReference('event-line-2', $eventLine);

        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 15;
    }
}
