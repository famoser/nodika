<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Production;

use App\Entity\EventTag;
use App\Enum\EventTagColor;
use App\Enum\EventTagType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadEventTag extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = 1;

    /**
     * Load data fixtures with the passed EntityManager.
     */
    public function load(ObjectManager $manager): void
    {
        $realExamples = [
            ['Notfalldienst', 'Sie kümmern sich um die Notfälle und nehmen die Anrufe der Notfalldienstnummer entgegen', EventTagColor::BLUE, EventTagType::ACTIVE_SERVICE],
            ['Wochentelefon', 'Sie kümmern sich um das Wochentelefon', EventTagColor::YELLOW, EventTagType::BACKUP_SERVICE],
        ];

        foreach ($realExamples as $realExample) {
            $eventLine = new EventTag();
            $eventLine->setName($realExample[0]);
            $eventLine->setDescription($realExample[1]);
            $eventLine->setColor($realExample[2]);
            $eventLine->setTagType($realExample[3]);
            $manager->persist($eventLine);
        }

        $manager->flush();
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
