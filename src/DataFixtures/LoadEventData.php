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
use App\Entity\EventLineGeneration;
use App\Entity\Person;
use App\Enum\DistributionType;
use App\Model\EventLineGeneration\RoundRobin\MemberConfiguration;
use App\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventData extends BaseFixture
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
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $generation = $this->getEventGenerationService();
        $roundRobinConfiguration = new RoundRobinConfiguration(null);

        $members = [$this->getReference('member-1'), $this->getReference('member-2'), $this->getReference('member-3')];
        $eventLines = [$this->getReference('event-line-1'), $this->getReference('event-line-2')];

        /* @var Person $admin */
        $admin = $this->getReference('person-1');

        $roundRobinConfiguration->memberConfigurations = [];
        for ($i = 0; $i < count($members); ++$i) {
            $roundRobinConfiguration->memberConfigurations[] = MemberConfiguration::createFromMember($members[$i], $i);
        }

        $roundRobinConfiguration->startDateTime = new \DateTime();
        $roundRobinConfiguration->endDateTime = new \DateTime('now + 1 year');
        $roundRobinConfiguration->conflictPufferInHours = 0;
        $roundRobinConfiguration->lengthInHours = 168;
        $roundRobinConfiguration->randomOrderMade = true;

        $output = $generation->generateRoundRobin($roundRobinConfiguration, function () {
            return true;
        });

        $eventLineGeneration = new EventLineGeneration();
        $eventLineGeneration->setEventLine($eventLines[0]);
        $eventLineGeneration->setCreatedAtDateTime(new \DateTime());
        $eventLineGeneration->setCreatedByPerson($admin);
        $eventLineGeneration->setDistributionConfiguration($roundRobinConfiguration);
        $eventLineGeneration->setDistributionOutput($output);
        $eventLineGeneration->setDistributionType(DistributionType::ROUND_ROBIN);
        $eventLineGeneration->setGenerationResult($output->generationResult);
        $manager->persist($eventLineGeneration);
        $generation->persist($eventLineGeneration, $output->generationResult, $admin);

        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 20;
    }
}
