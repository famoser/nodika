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
     * the buckets algorithm puts the targets into equal size buckets
     * according to the share of one target it secures that bucket with that probability
     * the targets are distributed to the buckets to be in as few buckets as possible.
     *
     * @param array $weightedTargets is an array of the form (targetId => relativeSize) (int => int)
     * @param int   $bucketsCount    the number of buckets to distribute
     *
     * @throws GenerationException
     *
     * @return array is an array of the form (targetId => bucketsAssigned) (int => int)
     */
    private function distributeToTargets($weightedTargets, $bucketsCount)
    {
        //result
        $targetBucketAssignments = [];

        //prepare parties
        $assignmentSizes = [];
        $totalSize = 0;
        foreach ($weightedTargets as $partyId => $targetSize) {
            //array indexes must be int; to avoid rounding errors multiply by 10000 first
            $assignmentSizes[(int) ($targetSize * 10000)][] = $partyId;
            $totalSize += $targetSize;
        }

        //calculate bucket size
        $bucketSize = (float) $totalSize / $bucketsCount;

        //## go once over all parties and distribute "full" buckets, so get rid of guaranteed assignments

        $distributedBuckets = 0;
        $missingAssignments = [];
        foreach ($assignmentSizes as $targetSize => $targetsOfThisSize) {
            //calculate how many full buckets
            $times = (int) $targetSize / $bucketSize;
            $newPartySize = (int) ($targetSize - $bucketSize * $times);

            //assign full buckets, and put rest in missingAssignments
            foreach ($targetsOfThisSize as $currentTargetId) {
                $missingAssignments[$newPartySize][] = $currentTargetId;
                $targetBucketAssignments[$currentTargetId] = $times;
                $distributedBuckets += $times;
            }
        }

        //## distribute part buckets, ensure the parties are in as few buckets as possible

        //sort by size so big parties are in one bucket for sure
        krsort($assignmentSizes);

        //create buckets
        $buckets = [];
        $bucketsNeeded = $bucketsCount - $distributedBuckets;
        for ($i = 0; $i < $bucketsNeeded; ++$i) {
            $buckets[$i] = $bucketSize;
        }

        //distribute parties to buckets
        $bucketTargets = [];
        foreach ($assignmentSizes as $targetSize => $targetsOfThisSize) {
            foreach ($targetsOfThisSize as $currentTargetId) {
                $currentPartSize = $targetSize;

                $maxIterations = 50000;
                while ($currentPartSize > 0) {
                    if ($maxIterations-- <= 0) {
                        //wops, no way! terminate I guess?
                        throw new GenerationException(GenerationStatus::TIMEOUT);
                    }

                    //find biggest remaining bucket
                    $biggestRemaining = 0;
                    $biggestRemainingIndex = 0;
                    for ($i = 0; $i < $bucketsCount; ++$i) {
                        if ($biggestRemaining < $buckets[$i]) {
                            $biggestRemaining = $buckets[$i];
                            $biggestRemainingIndex = $i;
                            if ($biggestRemaining === $bucketSize) {
                                break;
                            }
                        }
                    }

                    //check if party can be placed in bucket fully
                    //0.0001 is the accuracy threshold
                    if ($biggestRemaining + 0.0001 > $currentPartSize) {
                        $bucketTargets[$biggestRemainingIndex][$currentTargetId] = $currentPartSize;

                        //adapt bucket sizes
                        $buckets[$biggestRemainingIndex] -= $currentPartSize;

                        break;
                    }
                    //party does not fit fully into bucket, therefore we have to continue
                    $currentPartSize -= $biggestRemaining;

                    $bucketTargets[$biggestRemainingIndex][$currentTargetId] = $biggestRemaining;

                    //adapt bucket sizes
                    $buckets[$biggestRemainingIndex] = 0;
                }
            }
        }

        //## randomly assign a bucket to a containing target

        //shuffle array
        $bucketIds = array_keys($bucketTargets);
        shuffle($buckets);
        $step = $bucketSize / \count($buckets);
        $currentStep = 0;
        foreach ($bucketIds as $bucketId) {
            $targets = $bucketTargets[$bucketId];

            //sort by value size, so big parties are more likely to get assigned
            arsort($targets);
            $targetIds = array_keys($targets);

            //chose target inside threshold range
            $currentSize = 0;
            $chosenTarget = $targetIds[0];
            foreach ($targetIds as $targetId) {
                if ($currentSize > $currentStep) {
                    //take target from last iteration
                    break;
                }

                $chosenTarget = $targetId;
                $currentSize += $targets[$targetId];
            }

            //preserve result
            ++$targetBucketAssignments[$chosenTarget];

            //increase threshold
            $currentStep += $step;
        }

        return $targetBucketAssignments;
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
        $bufferEndDate = $eventGeneration->getEndDateTime();
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
            $dayOfWeek = $event->getStartDateTime()->format('N');
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
     * @param EventGenerationPreviewEvent[] $events
     * @param EventTarget[]                 $eventTargets
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
     * @param EventGenerationPreviewEvent $event
     * @param EventTarget[]               $eventTargets
     *
     * @return EventTarget|null
     */
    private function getPredeterminedEventTargetOfEvent(EventGenerationPreviewEvent $event, array $eventTargets)
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
                return $a[1] === $b[1] ? 0 : $a[1] < $b[1] ? 1 : -1;
            });

            //assign how many events of a certain type a specific target has to be assigned
            //start with the most rare event type, distribute & calculate score of the parties
            //adapt the weighting for the next most rare event type, and repeat
            $weightedDifference = [];
            for ($i = 0; $i < \count($sortedEventTypes); ++$i) {
                $eventType = $sortedEventTypes[$i][0];
                $count = $sortedEventTypes[$i][1];
                $currentWeightedTargets = $weightedTargets;

                //prepare for mismatch
                $expectedAssignmentPerWeight = $count * 1.0 / array_sum($currentWeightedTargets);

                //adapt weighting
                if ($i > 0) {
                    $expectedAssignmentPerWeightWeight = $expectedAssignmentPerWeight * $weights[$eventType];
                    foreach ($weightedDifference as $targetId => $difference) {
                        $currentWeightedTargets[$targetId] += $difference / $expectedAssignmentPerWeightWeight;
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
        $manager = $this->doctrine->getManager();
        foreach ($eventGeneration->getPreviewEvents() as $previewEvent) {
            $manager->remove($previewEvent);
        }
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

        $eventGeneration->setIsApplied(true);
        $manager->persist($eventGeneration);

        //commit
        $manager->flush();
    }
}
