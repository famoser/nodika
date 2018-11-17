<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\EventGeneration;
use App\Entity\EventGenerationPreviewEvent;
use App\Entity\EventPast;
use App\Entity\Traits\EventTrait;
use App\Enum\EventChangeType;
use App\Enum\EventType;
use App\Enum\GenerationStatus;
use App\EventGeneration\ConflictLookup;
use App\EventGeneration\EventTarget;
use App\EventGeneration\QueueGenerator;
use App\Exception\GenerationException;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\EventGenerationServiceInterface;
use Cron\CronExpression;
use Doctrine\Common\Persistence\ManagerRegistry;

class EventGenerationService implements EventGenerationServiceInterface
{
    const RANDOM_ACCURACY = 1000;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * distribute a fixed amount of elements to weighted targets.
     *
     * @param array $weightedTargets is an array of the form (targetId => relativeSize) (int => int)
     * @param int   $bucketsCount    the number of buckets to distribute
     *
     * @return array is an array of the form (targetId => bucketsAssigned) (int => int)
     */
    private function distributeToTargets($weightedTargets, $bucketsCount)
    {
        $queueGenerator = new QueueGenerator($weightedTargets);
        $res = [];
        for ($i = 0; $i < $bucketsCount; ++$i) {
            $targetId = $queueGenerator->getNext();
            if (!isset($res[$targetId])) {
                $res[$targetId] = 1;
            } else {
                ++$res[$targetId];
            }
        }

        return $res;
    }

    /**
     * creates the events to be assigned according to the cron values.
     *
     * @param EventGeneration $eventGeneration
     *
     * @return EventGenerationPreviewEvent[]|array
     */
    private function constructEvents(EventGeneration $eventGeneration)
    {
        $now = new \DateTime();

        $startExpression = CronExpression::factory($eventGeneration->getStartCronExpression());
        $currentStartDate = $startExpression->getNextRunDate($eventGeneration->getStartDateTime(), 0, true, $now->getTimezone()->getName());

        $endExpression = CronExpression::factory($eventGeneration->getEndCronExpression());
        $currentEndDate = $endExpression->getNextRunDate($currentStartDate, 0, false, $now->getTimezone()->getName());

        /* @var EventGenerationPreviewEvent[] $result */
        $result = [];
        while ($currentStartDate < $eventGeneration->getEndDateTime()) {
            $event = new EventGenerationPreviewEvent();
            $event->setStartDateTime($currentStartDate);
            $event->setEndDateTime($currentEndDate);
            $event->setGeneratedBy($eventGeneration);
            $result[] = $event;

            $currentStartDate = $startExpression->getNextRunDate($currentStartDate);
            $currentEndDate = $endExpression->getNextRunDate($currentEndDate);
        }

        return $result;
    }

    /**
     * creates the events to be assigned according to the cron values.
     *
     * @param EventGeneration $eventGeneration
     *
     * @return ConflictLookup
     */
    private function createConflictLookup(EventGeneration $eventGeneration)
    {
        $now = new \DateTime();

        //get start of buffer
        $startExpression = CronExpression::factory($eventGeneration->getStartCronExpression());
        $bufferStartDate = $startExpression->getPreviousRunDate($eventGeneration->getStartDateTime(), $eventGeneration->getConflictBufferInEventMultiples(), true, $now->getTimezone()->getName());

        //get end of buffer
        $bufferSize = $bufferStartDate->diff($eventGeneration->getStartDateTime());
        $bufferEndDate = clone $eventGeneration->getEndDateTime();
        for ($i = 0; $i < $eventGeneration->getConflictBufferInEventMultiples(); ++$i) {
            $bufferEndDate->add($bufferSize);
        }

        //get all affected events
        $searchModel = new SearchModel(SearchModel::NONE);
        $searchModel->setStartDateTime($bufferStartDate);
        $searchModel->setEndDateTime($bufferEndDate);
        $searchModel->setEventTags($eventGeneration->getConflictEventTags()->toArray());
        $events = $this->doctrine->getRepository(Event::class)->search($searchModel);

        //create conflict lookup
        return new ConflictLookup($events, $bufferSize);
    }

    /**
     * assigns the default event types (weekdays, saturdays, sundays).
     *
     * @param EventGenerationPreviewEvent[] $events
     */
    private function assignNaiveEventType(array $events)
    {
        foreach ($events as $event) {
            $dayOfWeek = (int) $event->getStartDateTime()->format('N');
            if (7 === $dayOfWeek) {
                $event->setEventType(EventType::SUNDAY);
            } elseif (6 === $dayOfWeek) {
                $event->setEventType(EventType::SATURDAY);
            } else {
                $event->setEventType(EventType::WEEKDAY);
            }
        }
    }

    /**
     * applies specified exceptions to algorithm.
     *
     * @param EventGeneration               $eventGeneration
     * @param EventGenerationPreviewEvent[] $events
     */
    private function processEventTypeExceptions(EventGeneration $eventGeneration, array $events)
    {
        foreach ($events as $event) {
            foreach ($eventGeneration->getDateExceptions() as $dateException) {
                //if inside the specified range
                if (
                    $event->getStartDateTime() >= $dateException->getStartDateTime() &&
                    $event->getStartDateTime() <= $dateException->getEndDateTime()
                ) {
                    //apply the special stuff
                    if (null !== $dateException->getEventType()) {
                        $event->setEventType($dateException->getEventType());
                    }
                }
            }
        }
    }

    /**
     * counts how often event types occurr.
     *
     * @param EventGenerationPreviewEvent[] $events
     *
     * @return array an array of the form (eventType => int)
     */
    private function countEventTypes(array $events)
    {
        $values = EventType::getPossibleValues();
        $counter = [];
        foreach ($values as $value) {
            $counter[$value] = 0;
        }

        foreach ($events as $event) {
            ++$counter[$event->getEventType()];
        }

        return $counter;
    }

    /**
     * @param EventGeneration $eventGeneration
     *
     * @return EventTarget[]
     */
    private function getEventTargets(EventGeneration $eventGeneration)
    {
        $targets = [];
        $currentId = 1;
        foreach ($eventGeneration->getDoctors() as $doctor) {
            $targets[$currentId] = EventTarget::fromDoctor($doctor);
            ++$currentId;
        }
        foreach ($eventGeneration->getClinics() as $clinic) {
            $targets[$currentId] = EventTarget::fromClinic($clinic);
            ++$currentId;
        }

        return $targets;
    }

    /**
     * @param EventTarget[] $eventTargets
     *
     * @return EventTarget[]
     */
    private function getOrderedEventTargetLookup(array $eventTargets)
    {
        //put in orderable array
        /** @var EventTarget[][] $orderable */
        $orderable = [];
        foreach ($eventTargets as $eventTarget) {
            $orderable[$eventTarget->getTarget()->getDefaultOrder()][] = $eventTarget;
        }

        ksort($orderable);

        $ordered = [];
        foreach ($orderable as $items) {
            foreach ($items as $item) {
                $ordered[$item->getIdentifier()] = $item;
            }
        }

        return $ordered;
    }

    /**
     * @param EventTarget[] $eventTargets
     *
     * @return array (int => float)
     */
    private function getWeigthedTargetArray(array $eventTargets)
    {
        $result = [];
        foreach ($eventTargets as $eventTarget) {
            $result[$eventTarget->getIdentifier()] = $eventTarget->getTarget()->getWeight();
        }

        return $result;
    }

    /**
     * @param EventGeneration $eventGeneration
     * @param $newEventCount
     * @param $newTargetCount
     *
     * @return array
     */
    private function getPreviousEvents(EventGeneration $eventGeneration, $newEventCount, $newTargetCount)
    {
        //the limit specifies how many events will have an influence to the generation
        //keep the number between 1000 & 10'000, ideally relative to the generation
        $limit = min($newEventCount * 2, $newTargetCount * 5, 10000);
        $limit = min(1000, $limit);

        $end = $eventGeneration->getStartDateTime();
        $searchModel = new SearchModel(SearchModel::NONE);
        $searchModel->setStartDateTime(((new \DateTime())->setTimestamp(0)));
        $searchModel->setEndDateTime($end);
        $searchModel->setMaxResults($limit);
        $searchModel->setEventTags($eventGeneration->getConflictEventTags());

        $events = $this->doctrine->getRepository(Event::class)->search($searchModel);

        return $events;
    }

    /**
     * @param EventTrait[]  $events
     * @param EventTarget[] $eventTargets
     *
     * @return array
     */
    private function eventsToWarmupArray(array $events, array $eventTargets)
    {
        $result = [];
        foreach ($events as $event) {
            $eventTarget = $this->getPredeterminedEventTargetOfEvent($event, $eventTargets);
            if (null !== $eventTarget) {
                $result[] = $eventTarget->getIdentifier();
            } else {
                $result[] = EventTarget::NONE_IDENTIFIER;
            }
        }

        return $result;
    }

    /** @var EventTarget[] $eventTargetDoctorLookup */
    private $eventTargetDoctorLookup = null;
    /** @var EventTarget[] $eventTargetClinicLookup */
    private $eventTargetClinicLookup = null;

    /**
     * @param EventTrait    $event
     * @param EventTarget[] $eventTargets
     *
     * @return EventTarget|null
     */
    private function getPredeterminedEventTargetOfEvent($event, array $eventTargets)
    {
        if (null === $this->eventTargetDoctorLookup) {
            $this->eventTargetDoctorLookup = [];
            $this->eventTargetClinicLookup = [];
            foreach ($eventTargets as $eventTarget) {
                if (null !== $eventTarget->getDoctor()) {
                    $this->eventTargetDoctorLookup[$eventTarget->getDoctor()->getId()] = $eventTarget;
                } elseif (null !== $eventTarget->getClinic()) {
                    $this->eventTargetClinicLookup[$eventTarget->getClinic()->getId()] = $eventTarget;
                }
            }
        }

        $clinics = $this->eventTargetClinicLookup;
        $doctors = $this->eventTargetDoctorLookup;

        if (null !== $event->getDoctor()) {
            if (isset($doctors[$event->getDoctor()->getId()])) {
                return $doctors[$event->getDoctor()->getId()];
            }
        }

        if (null !== $event->getClinic()) {
            if (isset($clinics[$event->getClinic()->getId()])) {
                return $clinics[$event->getClinic()->getId()];
            }
        }

        return null;
    }

    /**
     * generates the events as specified in the generation.
     *
     * @param EventGeneration $eventGeneration
     *
     * @throws GenerationException
     */
    public function generate(EventGeneration $eventGeneration)
    {
        //create events & fill out properties
        $events = $this->constructEvents($eventGeneration);
        if (0 === \count($events)) {
            return;
        }

        //get event targets
        $targets = $this->getEventTargets($eventGeneration);
        $targetLookup = $this->getOrderedEventTargetLookup($targets);
        if (0 === \count($targetLookup)) {
            return;
        }

        //get the order the event targets should be applied
        $weightedTargets = $this->getWeigthedTargetArray($targetLookup);
        $queueGenerator = new QueueGenerator($weightedTargets);
        if ($eventGeneration->getMindPreviousEvents()) {
            $previousEvents = $this->getPreviousEvents($eventGeneration, \count($events), \count($targets));
            $warmUpEvents = $this->eventsToWarmupArray($previousEvents, $targetLookup);
            $queueGenerator->warmUp($warmUpEvents);
        }

        //prepare event type to weight mapping
        $weights = [
            EventType::UNSPECIFIED => 1,
            EventType::WEEKDAY => $eventGeneration->getWeekdayWeight(),
            EventType::SATURDAY => $eventGeneration->getSaturdayWeight(),
            EventType::SUNDAY => $eventGeneration->getSundayWeight(),
            EventType::HOLIDAY => $eventGeneration->getHolidayWeight(),
        ];

        //assign events
        if ($eventGeneration->getDifferentiateByEventType()) {
            //assign event types
            $this->assignNaiveEventType($events);
            $this->processEventTypeExceptions($eventGeneration, $events);

            //determine how many events of each type the targets have to do be assigned
            $counter = $this->countEventTypes($events);
            $sortedEventTypes = [];
            foreach ($counter as $eventType => $count) {
                $sortedEventTypes[] = [$eventType, $count];
            }

            //sort descending by count
            usort($sortedEventTypes, function ($a, $b) {
                return $a[1] === $b[1] ? 0 : $a[1] < $b[1] ? -1 : 1;
            });

            //assign how many events of a certain type a specific target has to be assigned
            //start with the most rare event type, distribute & calculate score of the parties
            //adapt the weighting for the next most rare event type, and repeat
            $weightedDifference = [];
            for ($i = 0; $i < \count($sortedEventTypes); ++$i) {
                $eventType = $sortedEventTypes[$i][0];
                $count = $sortedEventTypes[$i][1];

                //skip if no events to distribute
                if (0 === $count) {
                    continue;
                }

                //adapt weighting
                $currentWeightedTargets = $weightedTargets;
                $expectedAssignmentPerWeight = $count * 1.0 / array_sum($currentWeightedTargets);
                if ($i > 0) {
                    foreach ($weightedDifference as $targetId => $difference) {
                        $targetAdditionalEventCount = $difference / $weights[$eventType];
                        $expectedEventCount = $expectedAssignmentPerWeight * $currentWeightedTargets[$targetId];
                        $currentWeightedTargets[$targetId] += ($targetAdditionalEventCount * 1.0 / $expectedEventCount);
                    }
                }

                //assign
                $assignment = $this->distributeToTargets($currentWeightedTargets, $count);
                foreach ($assignment as $targetId => $targetCount) {
                    $targetLookup[$targetId]->restrictEventTypeResponsibility($eventType, $targetCount);

                    //calculate difference between expected / real count
                    $absoluteDifference = ($expectedAssignmentPerWeight * $currentWeightedTargets[$targetId] - $targetCount);
                    $weightedDifference[$targetId] = $absoluteDifference * $weights[$eventType];
                }
            }
        }

        //process predetermined events
        foreach ($events as $event) {
            $target = $this->getPredeterminedEventTargetOfEvent($event, $targetLookup);
            if (null !== $target) {
                $target->assumeResponsibility($event->getEventType());
            }
        }

        //create the conflict lookup
        $conflictLookup = $this->createConflictLookup($eventGeneration);

        //distribute events
        $targetCount = \count($targets);
        foreach ($events as $event) {
            $target = $this->getPredeterminedEventTargetOfEvent($event, $targetLookup);
            if (null !== $target) {
                //sanity check for predetermined event target
                if (!$target->canAssumeResponsibility($event->getEventType()) || $conflictLookup->hasConflict($target, $event)) {
                    throw new GenerationException(GenerationStatus::PREDETERMINED_EVENT_CANT_BE_ASSIGNED);
                }
            }

            //try to find target if not specified
            if (null === $target) {
                //snapshot current queue state
                $queueGenerator->snapshot();

                //need to find target which supports that event type
                $targetsTried = [];
                do {
                    //stop if all targets have been tried
                    if (\count($targetsTried) === $targetCount) {
                        throw new GenerationException(GenerationStatus::NO_TARGET_CAN_ASSUME_RESPONSIBILITY);
                    }

                    //select next target to try
                    $targetId = $queueGenerator->getNext();
                    $target = $targetLookup[$targetId];
                    $targetsTried[$targetId] = true;
                } while (!$target->canAssumeResponsibility($event->getEventType()) || $conflictLookup->hasConflict($target, $event));

                //recover queue
                $queueGenerator->recoverSnapshot();
            }

            //save to queue
            $queueGenerator->forceNext($target->getIdentifier());

            //save to event
            $target->assumeResponsibility($event->getEventType());
            $event->setDoctor($target->getDoctor());
            $event->setClinic($target->getClinic());
        }

        //do statistics
        foreach ($targets as $target) {
            $target->getTarget()->setGenerationScore($target->calculateResponsibility($weights));
        }

        //replace generated events
        foreach ($eventGeneration->getPreviewEvents() as $previewEvent) {
            $previewEvent->setGeneratedBy(null);
        }
        $eventGeneration->getPreviewEvents()->clear();
        foreach ($events as $event) {
            $eventGeneration->getPreviewEvents()->add($event);
            $event->setGeneratedBy($eventGeneration);
        }
    }

    /**
     * @param EventGeneration $eventGeneration
     * @param Doctor          $creator
     */
    public function persist(EventGeneration $eventGeneration, Doctor $creator)
    {
        $manager = $this->doctrine->getManager();

        //create events
        foreach ($eventGeneration->getPreviewEvents() as $previewEvent) {
            $event = Event::create($previewEvent);

            //add past
            $eventPast = EventPast::create($event, EventChangeType::GENERATED, $creator);
            $event->getEventPast()->add($eventPast);

            //add tags
            foreach ($eventGeneration->getAssignEventTags() as $assignEventTag) {
                $event->getEventTags()->add($assignEventTag);
            }

            //add to db
            $manager->persist($event);
        }

        //apply & flush all
        $eventGeneration->setIsApplied(true);
        $manager->persist($eventGeneration);
        $manager->flush();
    }
}
