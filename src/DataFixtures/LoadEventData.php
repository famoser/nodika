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
use App\Entity\EventGeneration;
use App\Entity\Member;
use App\Entity\Person;
use App\Enum\DistributionType;
use App\Model\EventLineGeneration\RoundRobin\MemberConfiguration;
use App\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventData extends BaseFixture
{
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

        /**
         * @var Member $member1
         * @var Member $member2
         */
        $member1 = $this->getReference('member-1');
        $member2 = $this->getReference('member-2');
        $members = [$member1, $member2, $this->getReference('member-3')];
        $eventLines = [$this->getReference('event-line-1'), $this->getReference('event-line-2')];

        /* @var Person $admin */
        $admin = $this->getReference('person-1');

        $roundRobinConfiguration->memberConfigurations = [];
        for ($i = 0; $i < count($members); ++$i) {
            $roundRobinConfiguration->memberConfigurations[] = MemberConfiguration::createFromMember($members[$i], $i);
        }

        $roundRobinConfiguration->startDateTime = new \DateTime();
        $roundRobinConfiguration->endDateTime = new \DateTime('now + 4 months');
        $roundRobinConfiguration->conflictPufferInHours = 0;
        $roundRobinConfiguration->lengthInHours = 48;
        $roundRobinConfiguration->randomOrderMade = true;

        $output = $generation->generateRoundRobin($roundRobinConfiguration, function () {
            return true;
        });

        $eventLineGeneration = new EventGeneration();
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

        //assign events to member
        foreach ($member1->getEvents() as $event) {
            $event->setFrontendUser($member1->getFrontendUsers()->get(0));
            $manager->persist($event);
        }
        foreach ($member2->getEvents() as $event) {
            $event->setFrontendUser($member2->getFrontendUsers()->get(0));
            $manager->persist($event);
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
        return 20;
    }

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
}
