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
use App\Entity\EventPast;
use App\Enum\EventChangeType;
use App\Enum\EventType;
use App\Enum\GenerationStatus;
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
     * creates a lucky score.
     *
     * @param $totalPoints
     * @param $reachedPoints
     *
     * @return float
     */
    private function convertToLuckyScore($totalPoints, $reachedPoints)
    {
        return ($reachedPoints / $totalPoints) * 100.0;
    }

    /**
     * creates a lucky score.
     *
     * @param float $totalPoints
     * @param float $luckyScore
     *
     * @return int
     */
    private function convertFromLuckyScore($totalPoints, $luckyScore)
    {
        $realScore = $luckyScore / 100.0;

        return $totalPoints * $realScore;
    }

    /**
     * distribute the days to the clinics.
     *
     * @param array $partiesArray         is an array of the form (int => double) (target points per clinic)
     * @param array $distributedDaysArray is an array of the form (int => (int => int)) (distributed dayKey => dayCount per clinic)
     * @param float $dayValue             the value of this day for $distributedPointsArray calculation
     * @param int   $dayCount             the amount of
     * @param int   $dayKey               the key of this day used in $distributedDaysArray
     *
     * @throws GenerationException
     */
    private function distributeDays(&$partiesArray, &$distributedDaysArray, $dayValue, $dayCount, $dayKey)
    {
        //prepare party sizes
        $myParties = [];
        foreach ($partiesArray as $partyId => $partySize) {
            $myParties[$partyId] = (int) ($partySize * 10000);
        }

        //get assignment
        $bucketAssignment = $this->bucketsAlgorithm($myParties, $dayCount);

        //add points to $distributedPointsArray
        foreach ($bucketAssignment as $clinicId => $bucketCount) {
            $partiesArray[$clinicId] -= $dayValue * $bucketCount;
            $distributedDaysArray[$clinicId][$dayKey] += $bucketCount;
        }
    }

    /**
     * distribute a fixed amount of elements to weighted parties.
     *
     * the buckets algorithm puts the parties into equal size buckets
     * according to the share of one party it secures that bucket with that probability
     * the parties are distributed to the buckets to be in as few buckets as possible.
     *
     * @param array $parties      is an array of the form (partyId => relativeSize) (int => int)
     * @param int   $bucketsCount the number of buckets to distribute
     *
     * @throws GenerationException
     *
     * @return array is an array of the form (partyId => bucketsAssigned) (int => int)
     */
    private function bucketsAlgorithm($parties, $bucketsCount)
    {
        //result
        $partyBucketAssignments = [];

        //prepare parties
        $assignmentSizes = [];
        $totalSize = 0;
        foreach ($parties as $partyId => $partySize) {
            $assignmentSizes[$partySize][] = $partyId;
            $totalSize += $partySize;
        }

        //calculate bucket size
        $bucketSize = (float) $totalSize / $bucketsCount;

        //## go once over all parties and distribute "full" buckets, so get rid of guaranteed assignments

        $distributedBuckets = 0;
        $missingAssignments = [];
        foreach ($assignmentSizes as $partySize => $partiesOfThisSize) {
            //calculate how many full buckets
            $times = (int) $partySize / $bucketSize;
            $newPartySize = (int) ($partySize - $bucketSize * $times);

            //assign full buckets, and put rest in missingAssignments
            foreach ($partiesOfThisSize as $currentPartyId) {
                $missingAssignments[$newPartySize][] = $currentPartyId;
                $partyBucketAssignments[$currentPartyId] = $times;
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
        $bucketClinics = [];
        foreach ($assignmentSizes as $partySize => $partiesOfThisSize) {
            foreach ($partiesOfThisSize as $currentPartyId) {
                $currentPartSize = $partySize;

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
                        $bucketClinics[$biggestRemainingIndex][$currentPartyId] = $currentPartSize;

                        //adapt bucket sizes
                        $buckets[$biggestRemainingIndex] -= $currentPartSize;

                        break;
                    }
                    //party does not fit fully into bucket, therefore we have to continue
                    $currentPartSize -= $biggestRemaining;

                    $bucketClinics[$biggestRemainingIndex][$currentPartyId] = $biggestRemaining;

                    //adapt bucket sizes
                    $buckets[$biggestRemainingIndex] = 0;
                }
            }
        }

        //## randomly assign a bucket to a containing clinic

        //shuffle array
        $bucketIds = array_keys($bucketClinics);
        shuffle($buckets);
        $step = $bucketSize / \count($buckets);
        $currentStep = 0;
        foreach ($bucketIds as $bucketId) {
            $clinics = $bucketClinics[$bucketId];

            //sort by value size, so big parties are more likely to get assigned
            arsort($clinics);
            $clinicIds = array_keys($clinics);

            //chose clinic inside threshold range
            $currentSize = 0;
            $chosenClinic = $clinicIds[0];
            foreach ($clinicIds as $clinicId) {
                if ($currentSize > $currentStep) {
                    //take clinic from last iteration
                    break;
                }

                $chosenClinic = $clinicId;
                $currentSize += $clinics[$clinicId];
            }

            //preserve result
            ++$partyBucketAssignments[$chosenClinic];

            //increase threshold
            $currentStep += $step;
        }

        return $partyBucketAssignments;
    }

    private function constructEvents(EventGeneration $eventGeneration)
    {
        $now = new \DateTime();

        $startExpression = CronExpression::factory($eventGeneration->getStartCronExpression());
        $currentStartDate = $startExpression->getNextRunDate($eventGeneration->getStartDateTime(), 0, true, $now->getTimezone()->getName());

        $endExpression = CronExpression::factory($eventGeneration->getEndCronExpression());
        $currentEndDate = $endExpression->getNextRunDate($currentStartDate, 0, false, $now->getTimezone()->getName());

        /* @var Event[] $result */
        $result = [];
        while ($currentStartDate < $eventGeneration->getEndDateTime()) {
            $event = new Event();
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
     * assigns the default event types (weekdays, saturdays, sundays).
     *
     * @param Event[] $events
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
     * @param EventGeneration $eventGeneration
     * @param Event[]         $events
     */
    private function processExceptions(EventGeneration $eventGeneration, array $events)
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
    private function orderEventTargets(array $eventTargets)
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
    private function getClinicRelativeSizeArray(array $eventTargets)
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

        $eventLines = $this->doctrine->getRepository(Event::class)->search($searchModel);
        $events = [];
        foreach ($eventLines as $eventLine) {
            foreach ($eventLine->events as $event) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * @param Event[]       $events
     * @param EventTarget[] $eventTargets
     *
     * @return array
     */
    private function eventsToWarmupArray(array $events, array $eventTargets)
    {
        $result = [];
        foreach ($events as $event) {
            $eventTarget = $this->getEventTargetOfEvent($event, $eventTargets);
            if (null !== $eventTarget) {
                $result[] = $eventTarget->getIdentifier();
            } else {
                $result[] = $eventTarget::NONE_IDENTIFIER;
            }
        }

        return $result;
    }

    /** @var EventTarget[] $eventTargetDoctorCache */
    private $eventTargetDoctorCache = null;
    /** @var EventTarget[] $eventTargetClinicCache */
    private $eventTargetClinicCache = null;

    /**
     * @param Event         $event
     * @param EventTarget[] $eventTargets
     *
     * @return EventTarget|null
     */
    private function getEventTargetOfEvent(Event $event, array $eventTargets)
    {
        if (null === $this->eventTargetDoctorCache) {
            $this->eventTargetDoctorCache = [];
            $this->eventTargetClinicCache = [];
            foreach ($eventTargets as $eventTarget) {
                if (null !== $eventTarget->getDoctor()) {
                    $this->eventTargetDoctorCache[$eventTarget->getDoctor()->getId()] = $eventTarget;
                } elseif (null !== $eventTarget->getClinic()) {
                    $this->eventTargetClinicCache[$eventTarget->getClinic()->getId()] = $eventTarget;
                }
            }
        }

        $clinics = $this->eventTargetClinicCache;
        $doctors = $this->eventTargetDoctorCache;

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
     * @return Event[]
     */
    public function generate(EventGeneration $eventGeneration)
    {
        //create events & fill out properties
        $events = $this->constructEvents($eventGeneration);
        $this->assignNaiveEventType($events);
        $this->processExceptions($eventGeneration, $events);
        if (0 === \count($events)) {
            return $events;
        }

        //get event targets
        $targets = $this->getEventTargets($eventGeneration);
        $orderedTargets = $this->orderEventTargets($targets);
        if (0 === \count($orderedTargets)) {
            return $events;
        }

        //get the order the event targets should be applied
        $queueGenerator = new QueueGenerator($this->getClinicRelativeSizeArray($orderedTargets));
        if ($eventGeneration->getMindPreviousEvents()) {
            $previousEvents = $this->getPreviousEvents($eventGeneration, \count($events), \count($targets));
            $warmUpEvents = $this->eventsToWarmupArray($previousEvents, $orderedTargets);
            $queueGenerator->warmUp($warmUpEvents);
        }

        //assign events
        if (!$eventGeneration->getDifferentiateByEventType()) {
            foreach ($events as $event) {
                $target = $this->getEventTargetOfEvent($event, $orderedTargets);
                if (null === $target) {
                    $nextQueue = $queueGenerator->getNext();
                    $target = $orderedTargets[$nextQueue];
                    $event->setDoctor($target->getDoctor());
                    $event->setClinic($target->getClinic());
                } else {
                    $queueGenerator->forceNext($target->getIdentifier());
                }
            }
        }

        return $events;
    }

    /**
     * @param EventGeneration $eventGeneration
     * @param Doctor          $creator
     */
    public function persistEvents(EventGeneration $eventGeneration, Doctor $creator)
    {
        $manager = $this->doctrine->getManager();

        //generate events & add to db
        $events = $this->generate($eventGeneration);
        foreach ($events as $event) {
            //add past
            $eventPast = EventPast::create($event, EventChangeType::GENERATED, $creator);
            $event->getEventPast()->add($eventPast);

            //add tagw
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
