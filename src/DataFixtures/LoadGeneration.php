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

use App\DataFixtures\Production\LoadEventTag;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\EventGeneration;
use App\Entity\EventGenerationDateException;
use App\Entity\EventGenerationTargetClinic;
use App\Entity\EventTag;
use App\Enum\EventType;
use App\Helper\DateTimeFormatter;
use App\Service\Interfaces\EventGenerationServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadGeneration extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = LoadSetting::ORDER + LoadClinic::ORDER + LoadDoctor::ORDER + LoadEventTag::ORDER + 1;

    public function __construct(private readonly EventGenerationServiceInterface $eventGenerationService)
    {
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $cronExpressions = ['0 8 * * *', '0 8 */7 * *'];
        $expressionIndex = 0;

        $tags = $manager->getRepository(EventTag::class)->findAll();
        $admin = $manager->getRepository(Doctor::class)->findOneBy(['isAdministrator' => true]);
        foreach ($tags as $tag) {
            $this->generateForTag($tag, $admin, $manager, $cronExpressions[$expressionIndex++ % 2], 1 === $expressionIndex % 2);
        }
    }

    private function generateForTag(EventTag $tag, Doctor $admin, ObjectManager $manager, string $cronExpression, bool $differentiate): void
    {
        // prepare a generation
        $generation = new EventGeneration();
        $generation->registerChangeBy($admin);
        $generation->setName('example generation at '.(new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT).' '.$differentiate);
        $generation->setDifferentiateByEventType($differentiate);
        $generation->setStartDateTime(new \DateTime('today + 8 hours'));
        $generation->setEndDateTime(new \DateTime('today + 8 hours + 1 year'));
        $generation->setStartCronExpression($cronExpression);
        $generation->setEndCronExpression($cronExpression);
        $generation->getAssignEventTags()->add($tag);

        // date exceptions
        $dateExceptions = [
            [EventType::HOLIDAY, EventType::HOLIDAY, EventType::HOLIDAY, EventType::HOLIDAY, EventType::HOLIDAY, EventType::HOLIDAY],
        ];
        foreach ($dateExceptions as $index => $dateException) {
            $exception = new EventGenerationDateException();
            $exception->setStartDateTime(new \DateTime('yesterday - ' + (-1) + ' day'));
            $exception->setEndDateTime(new \DateTime('yesterday - ' + ($index * 5) + ' day'));
            $exception->setEventType($dateException[0]);
            $exception->setEventGeneration($generation);
        }

        // add most clinics as generation target
        $clinics = $manager->getRepository(Clinic::class)->findAll();
        $skipPossibility = \count($clinics) / 3 * 2;
        $counter = 0;
        foreach ($clinics as $clinic) {
            if (0 !== $counter % $skipPossibility) {
                $target = new EventGenerationTargetClinic();
                $target->setWeight($counter % $skipPossibility + 2);
                $target->setClinic($clinic);
                $target->setEventGeneration($generation);
                $manager->persist($target);

                $generation->getClinics()->add($target);
            }
            ++$counter;
        }

        // save generation
        $manager->persist($generation);
        $manager->flush();

        // generate & persist all events
        $admin = $manager->getRepository(Doctor::class)->findOneBy(['isAdministrator' => true]);
        $this->eventGenerationService->generate($generation);
        $this->eventGenerationService->persist($generation, $admin);

        $events = $manager->getRepository(Event::class)->findAll();
        // confirm first 10 events
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
}
