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
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\EventGeneration;
use App\Entity\EventGenerationDateException;
use App\Entity\EventGenerationTargetClinic;
use App\Entity\EventTag;
use App\Enum\EventType;
use App\Helper\DateTimeFormatter;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGeneration extends BaseFixture
{
    const ORDER = LoadSetting::ORDER + LoadClinic::ORDER + LoadDoctor::ORDER + LoadEventTag::ORDER;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $cronExpressions = ['0 8 * * *', '0 8 */7 * *'];
        $expressionIndex = 0;

        $tags = $manager->getRepository(EventTag::class)->findAll();
        foreach ($tags as $tag) {
            $this->generateForTag($tag, $manager, $cronExpressions[$expressionIndex++ % 2]);
        }
    }

    /**
     * @param EventTag      $tag
     * @param ObjectManager $manager
     */
    private function generateForTag(EventTag $tag, ObjectManager $manager, $cronExpression)
    {
        //prepare a generation
        $generation = $this->getRandomInstance();
        $generation->setName('example generation at '.(new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT));
        $generation->setDifferentiateByEventType(false);
        $generation->setStartDateTime(new \DateTime());
        $generation->setEndDateTime(new \DateTime('now + 1 year'));
        $generation->setStartCronExpression($cronExpression);
        $generation->setEndCronExpression($cronExpression);
        $generation->getAssignEventTags()->add($tag);

        //date exceptions
        $dateExceptions = [
            [EventType::HOLIDAY, EventType::HOLIDAY, EventType::HOLIDAY, EventType::HOLIDAY, EventType::HOLIDAY, EventType::HOLIDAY],
        ];
        foreach ($dateExceptions as $dateException) {
            $exception = new EventGenerationDateException();
            $this->fillStartEnd($exception);
            $exception->setEventType($dateException[0]);
            $exception->setEventGeneration($generation);
        }

        //add most clinics as generation target
        $clinics = $manager->getRepository(Clinic::class)->findAll();
        $skipPossibility = \count($clinics) / 3 * 2;
        foreach ($clinics as $clinic) {
            if (0 !== rand(0, $skipPossibility)) {
                $target = new EventGenerationTargetClinic();
                $target->setClinic($clinic);
                $target->setEventGeneration($generation);
                $manager->persist($target);

                $generation->getClinics()->add($target);
            }
        }

        //save generation
        $manager->persist($generation);
        $manager->flush();

        //generate & persist all events
        $admin = $manager->getRepository(Doctor::class)->findOneBy(['isAdministrator' => true]);
        $events = $this->getEventGenerationService()->persist($generation, $admin);

        //confirm first 10 events
        for ($i = 0; $i < 10; ++$i) {
            $event = $events[$i];
            if ($event->getClinic()->getDoctors()->count() > 0) {
                $event->confirm($event->getClinic()->getDoctors()->first());
            }
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

    /**
     * create an instance with all random values.
     *
     * @return EventGeneration
     */
    protected function getRandomInstance()
    {
        $eventGeneration = new EventGeneration();
        $this->fillThing($eventGeneration);

        return $eventGeneration;
    }
}
