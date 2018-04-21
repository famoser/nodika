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
use App\Entity\EventTag;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventTag extends BaseFixture
{
    const ORDER = 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $realExamples = [
            ['Notfalldienst', 'Sie kümmern sich um die Notfälle und nehmen die Anrufe der Notfalldienstnummer entgegen'],
            ['Wochentelefon', 'Sie kümmern sich um das Wochentelefon'],
        ];

        foreach ($realExamples as $realExample) {
            $eventLine = $this->getRandomInstance();
            $eventLine->setName($realExample[0]);
            $eventLine->setDescription($realExample[1]);
            $manager->persist($eventLine);
        }

        $manager->flush();
    }

    /**
     * create an instance with all random values.
     *
     * @return EventTag
     */
    protected function getRandomInstance()
    {
        $eventLine = new EventTag();
        $this->fillThing($eventLine);

        return $eventLine;
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return static::ORDER;
    }
}
