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

use App\Entity\Event;
use App\Entity\EventGeneration;
use App\Enum\EventType;
use App\Enum\GenerationStatus;
use App\EventGeneration\EventTarget;
use App\EventGeneration\QueueGenerator;
use App\Exception\GenerationException;
use App\Model\EventGenerationService\IdealQueueMember;
use App\Service\Interfaces\EventGenerationServiceInterface;
use Cron\CronExpression;

class EventGenerationService implements EventGenerationServiceInterface
{
    const RANDOM_ACCURACY = 1000;

    /**
     * tries to generate the events
     * returns true if successful.
     *
     * @param RoundRobinConfiguration $roundRobinConfiguration
     * @param callable $memberAllowedCallable with arguments $startDateTime, $endDateTime, $member which returns a boolean if the event can happen
     *
     * @return RoundRobinOutput
     */
    public function generateRoundRobin(RoundRobinConfiguration $roundRobinConfiguration, $memberAllowedCallable)
    {
        $generationResult = new GenerationResult(null);
        $generationResult->generationDateTime = new \DateTime();

        $roundRobinResult = new RoundRobinOutput();
        $roundRobinResult->version = 1;

        $conflictCallable = $this->buildConflictBuffer($roundRobinConfiguration);

        /* @var RRMemberConfiguration[] $members */
        $members = [];
        foreach ($roundRobinConfiguration->memberConfigurations as $memberConfiguration) {
            if ($memberConfiguration->isEnabled) {
                $members[$memberConfiguration->order] = $memberConfiguration;
            }
        }
        //sorts by key
        ksort($members);
        $members = array_values($members);

        $assignedEventCount = 0;
        $activeIndex = 0;
        $totalMembers = count($members);
        /* @var RRMemberConfiguration[] $priorityQueue */
        $priorityQueue = [];
        $currentDate = clone $roundRobinConfiguration->startDateTime;
        while ($currentDate < $roundRobinConfiguration->endDateTime) {
            $endDate = clone $currentDate;
            $endDate = $this->addInterval($endDate, $roundRobinConfiguration);
            //check if something in priority queue
            /* @var RRMemberConfiguration $matchMember */
            $matchMember = null;
            if (count($priorityQueue) > 0) {
                $i = 0;
                for (; $i < count($priorityQueue); ++$i) {
                    if (
                        $memberAllowedCallable($currentDate, $endDate, $assignedEventCount, $priorityQueue[$i]) &&
                        $conflictCallable($assignedEventCount, $priorityQueue[$i])
                    ) {
                        $matchMember = $priorityQueue[$i];
                        break;
                    }
                }
                if (null !== $matchMember) {
                    unset($priorityQueue[$i]);
                    //reset keys in array (0,1,2,3,4,...)
                    $priorityQueue = array_values($priorityQueue);
                }
            }
            if (null === $matchMember) {
                $startIndex = $activeIndex;
                while (true) {
                    $myMember = $members[$activeIndex];
                    if ($memberAllowedCallable($currentDate, $endDate, $assignedEventCount, $myMember) &&
                        $conflictCallable($assignedEventCount, $myMember)) {
                        $matchMember = $myMember;
                        ++$activeIndex;
                        break;
                    }
                    $priorityQueue[] = $myMember;
                    ++$activeIndex;
                    //wrap around index
                    if ($activeIndex >= $totalMembers) {
                        $activeIndex = 0;
                    }
                    if ($startIndex === $activeIndex) {
                        return $this->returnRoundRobinError($roundRobinResult, RoundRobinStatusCode::NO_MATCHING_MEMBER);
                    }
                }
                //wrap around index
                if ($activeIndex >= $totalMembers) {
                    $activeIndex = 0;
                }
            }

            if (null === $matchMember) {
                return $this->returnRoundRobinError($roundRobinResult, RoundRobinStatusCode::NO_MATCHING_MEMBER_2);
            }
            $event = new GeneratedEvent();
            $event->memberId = $matchMember->id;
            $event->startDateTime = $currentDate;
            $event->endDateTime = $endDate;
            $generationResult->events[] = $event;
            ++$assignedEventCount;

            $currentDate = clone $endDate;
        }

        //prepare RR result
        $roundRobinResult->endDateTime = $currentDate;
        $roundRobinResult->lengthInHours = $roundRobinConfiguration->lengthInHours;
        $roundRobinResult->memberConfiguration = $members;
        $roundRobinResult->priorityQueue = $priorityQueue;
        $roundRobinResult->activeIndex = $activeIndex;
        $roundRobinResult->generationResult = $generationResult;

        return $this->returnRoundRobinSuccess($roundRobinResult);
    }

    /**
     * @param BaseConfiguration $configuration
     *
     * @return \Closure with arguments ($currentEventCount, $memberId)
     */
    private function buildConflictBuffer(BaseConfiguration $configuration)
    {
        /* @var [][] $allEventLineEvents */
        $allEventLineEvents = [];
        $conflictPufferInSeconds = $configuration->conflictPufferInHours * 60 * 60;
        foreach ($configuration->eventLineConfiguration as $item) {
            if ($item->isEnabled) {
                $eventLineEvents = [];
                foreach ($item->eventEntries as $eventEntry) {
                    $myArr = [];
                    $myArr['start'] = $eventEntry->startDateTime->getTimestamp() - $conflictPufferInSeconds;
                    $myArr['end'] = $eventEntry->endDateTime->getTimestamp() + $conflictPufferInSeconds;
                    $myArr['id'] = $eventEntry->memberId;
                    $eventLineEvents[$eventEntry->startDateTime->getTimestamp()][] = $myArr;
                }
                ksort($eventLineEvents);
                $collapsedArray = call_user_func_array('array_merge', $eventLineEvents);
                $allEventLineEvents[] = $collapsedArray;
            }
        }

        $eventLineCount = count($allEventLineEvents);
        $activeIndexes = [];
        $eventLineCounts = [];
        for ($i = 0; $i < $eventLineCount; ++$i) {
            $activeIndexes[$i] = 0;
            $eventLineCounts[$i] = count($allEventLineEvents[$i]);
        }

        $conflictBuffer = [];
        $assignedEventCount = 0;

        $currentDate = clone $configuration->startDateTime;
        while ($currentDate < $configuration->endDateTime) {
            $endDate = clone $currentDate;
            $endDate = $this->addInterval($endDate, $configuration);
            $startTimeStamp = $currentDate->getTimestamp();
            $endTimeStamp = $endDate->getTimestamp();
            $currentConflictBuffer = [];
            for ($i = 0; $i < $eventLineCount; ++$i) {
                for ($j = $activeIndexes[$i]; $j < $eventLineCounts[$i]; ++$j) {
                    $currentEvent = $allEventLineEvents[$i][$j];
                    if ($currentEvent['end'] < $startTimeStamp) {
                        //not in critical zone yet
                        ++$activeIndexes[$i];
                    } else {
                        //our active event begins before $currentEvent
                        if ($currentEvent['start'] >= $startTimeStamp) {
                            //so it must end inside or after $currentEvent
                            if (($currentEvent['start'] <= $endTimeStamp && $currentEvent['end'] >= $endTimeStamp) ||
                                $currentEvent['end'] <= $endTimeStamp) {
                                $currentConflictBuffer[] = $currentEvent['id'];
                                continue;
                            }
                        }
                        //our active events begins between $currentEvent
                        if ($currentEvent['start'] <= $startTimeStamp && $currentEvent['end'] >= $startTimeStamp) {
                            $currentConflictBuffer[] = $currentEvent['id'];
                            continue;
                        }

                        //no more assignment found; stop loop
                        break;
                    }
                }
            }

            $conflictBuffer[$assignedEventCount] = $currentConflictBuffer;
            ++$assignedEventCount;
            $currentDate = $endDate;
        }

        $myFunc = function ($currentEventCount, $member) use ($conflictBuffer) {
            /* @var BaseMemberConfiguration $member */
            return !in_array($member->id, $conflictBuffer[$currentEventCount], true);
        };

        return $myFunc;
    }

    private function addInterval(\DateTime $dateTime, BaseConfiguration $configuration)
    {
        $hours = $configuration->lengthInHours;
        $days = 0;
        while ($hours >= 24) {
            ++$days;
            $hours -= 24;
        }

        if ($hours >= 12) {
            ++$days;
            $hours = 24 - $hours;
            $daysAddInterval = new \DateInterval('P' . $days . 'D');
            $dateTime->add($daysAddInterval);
            $hoursRemoveInterval = new \DateInterval('PT' . $hours . 'H');
            $dateTime->sub($hoursRemoveInterval);
        } else {
            if ($days > 0) {
                $daysAddInterval = new \DateInterval('P' . $days . 'D');
                $dateTime->add($daysAddInterval);
            }
            if ($hours > 0) {
                $hoursAddInterval = new \DateInterval('PT' . $hours . 'H');
                $dateTime->sub($hoursAddInterval);
            }
        }

        return $dateTime;
    }

    /**
     * @param NodikaConfiguration $nodikaConfiguration
     *
     * @return bool
     */
    public function setEventTypeDistribution(NodikaConfiguration $nodikaConfiguration)
    {
        /* @var NMemberConfiguration[] $enabledMembers */
        $enabledMembers = [];
        foreach ($nodikaConfiguration->memberConfigurations as $memberConfiguration) {
            if ($memberConfiguration->isEnabled) {
                $enabledMembers[] = $memberConfiguration;
            }
        }

        //count day types
        $weekdayCount = 0;
        $saturdayCount = 0;
        $sundayCount = 0;
        $holidayCount = 0;

        $holidays = [];
        foreach ($nodikaConfiguration->holidays as $holiday) {
            $holidays[(new \DateTime($holiday->format('d.m.Y')))->getTimestamp()] = 1;
        }

        $currentDate = clone $nodikaConfiguration->startDateTime;
        $oneMore = 1;
        while ($currentDate < $nodikaConfiguration->endDateTime || $oneMore--) {
            $day = new \DateTime($currentDate->format('d.m.Y'));
            if (isset($holidays[$day->getTimestamp()])) {
                ++$holidayCount;
            } else {
                $dayOfWeek = $day->format('N');
                if (7 === $dayOfWeek) {
                    ++$sundayCount;
                } elseif (6 === $dayOfWeek) {
                    ++$saturdayCount;
                } else {
                    ++$weekdayCount;
                }
            }

            $currentDate = $this->addInterval($currentDate, $nodikaConfiguration);
        }

        //count total points
        $eventTypeAssignment = $nodikaConfiguration->eventTypeConfiguration;
        $totalPoints = $holidayCount * $eventTypeAssignment->holiday;
        $totalPoints += $sundayCount * $eventTypeAssignment->sunday;
        $totalPoints += $saturdayCount * $eventTypeAssignment->saturday;
        $totalPoints += $weekdayCount * $eventTypeAssignment->weekday;

        $totalMemberPoints = 0;
        foreach ($enabledMembers as $enabledMember) {
            $totalMemberPoints += $enabledMember->points;
        }

        $pointsPerMemberPoint = $totalPoints / $totalMemberPoints;

        //initialize partiesArray to distribute days with the bucket algorithm
        $partiesArray = [];
        $distributedDaysArray = [];
        foreach ($enabledMembers as $memberConfiguration) {
            $partiesArray[$memberConfiguration->id] = $pointsPerMemberPoint * $memberConfiguration->points;
            $partiesArray[$memberConfiguration->id] += $this->convertFromLuckyScore($totalPoints, $memberConfiguration->luckyScore);
            $distributedDaysArray[$memberConfiguration->id] = [];
            $distributedDaysArray[$memberConfiguration->id][0] = 0;
            $distributedDaysArray[$memberConfiguration->id][1] = 0;
            $distributedDaysArray[$memberConfiguration->id][2] = 0;
            $distributedDaysArray[$memberConfiguration->id][3] = 0;
        }

        //distribute days to parties
        $this->distributeDays($partiesArray, $distributedDaysArray, $eventTypeAssignment->holiday, $holidayCount, 3);
        $this->distributeDays($partiesArray, $distributedDaysArray, $eventTypeAssignment->sunday, $sundayCount, 2);
        $this->distributeDays($partiesArray, $distributedDaysArray, $eventTypeAssignment->saturday, $saturdayCount, 1);
        $this->distributeDays($partiesArray, $distributedDaysArray, $eventTypeAssignment->weekday, $weekdayCount, 0);

        //create configurations
        $nodikaConfiguration->memberEventTypeDistributions = [];
        foreach ($enabledMembers as $enabledMember) {
            $memberEventTypeDistribution = new MemberEventTypeDistribution(null);
            $member = clone $enabledMember;
            $member->endScore = round($partiesArray[$enabledMember->id], 2);
            $member->luckyScore = round($this->convertToLuckyScore($totalPoints, $member->points), 2);
            $memberEventTypeDistribution->newMemberConfiguration = $member;

            $eventTypeAssignment = new EventTypeConfiguration(null);
            $eventTypeAssignment->holiday = $distributedDaysArray[$enabledMember->id][3];
            $eventTypeAssignment->sunday = $distributedDaysArray[$enabledMember->id][2];
            $eventTypeAssignment->saturday = $distributedDaysArray[$enabledMember->id][1];
            $eventTypeAssignment->weekday = $distributedDaysArray[$enabledMember->id][0];
            $memberEventTypeDistribution->eventTypeAssignment = $eventTypeAssignment;

            $nodikaConfiguration->memberEventTypeDistributions[] = $memberEventTypeDistribution;
        }

        return true;
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
     * distribute the days to the members.
     *
     * @param array $partiesArray is an array of the form (int => double) (target points per member)
     * @param array $distributedDaysArray is an array of the form (int => (int => int)) (distributed dayKey => dayCount per member)
     * @param float $dayValue the value of this day for $distributedPointsArray calculation
     * @param int $dayCount the amount of
     * @param int $dayKey the key of this day used in $distributedDaysArray
     * @throws GenerationException
     */
    private function distributeDays(&$partiesArray, &$distributedDaysArray, $dayValue, $dayCount, $dayKey)
    {
        //prepare party sizes
        $myParties = [];
        foreach ($partiesArray as $partyId => $partySize) {
            $myParties[$partyId] = (int)($partySize * 10000);
        }

        //get assignment
        $bucketAssignment = $this->bucketsAlgorithm($myParties, $dayCount);

        //add points to $distributedPointsArray
        foreach ($bucketAssignment as $memberId => $bucketCount) {
            $partiesArray[$memberId] -= $dayValue * $bucketCount;
            $distributedDaysArray[$memberId][$dayKey] += $bucketCount;
        }
    }

    /**
     * distribute a fixed amount of elements to weighted parties
     *
     * the buckets algorithm puts the parties into equal size buckets
     * according to the share of one party it secures that bucket with that probability
     * the parties are distributed to the buckets to be in as few buckets as possible.
     *
     * @param array $parties is an array of the form (partyId => relativeSize) (int => int)
     * @param int $bucketsCount the number of buckets to distribute
     *
     * @return array is an array of the form (partyId => bucketsAssigned) (int => int)
     * @throws GenerationException
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
        $bucketSize = (float)$totalSize / $bucketsCount;

        ### go once over all parties and distribute "full" buckets, so get rid of guaranteed assignments

        $distributedBuckets = 0;
        $missingAssignments = [];
        foreach ($assignmentSizes as $partySize => $partiesOfThisSize) {
            //calculate how many full buckets
            $times = (int)$partySize / $bucketSize;
            $newPartySize = (int)($partySize - $bucketSize * $times);

            //assign full buckets, and put rest in missingAssignments
            foreach ($partiesOfThisSize as $currentPartyId) {
                $missingAssignments[$newPartySize][] = $currentPartyId;
                $partyBucketAssignments[$currentPartyId] = $times;
                $distributedBuckets += $times;
            }
        }

        ### distribute part buckets, ensure the parties are in as few buckets as possible

        //sort by size so big parties are in one bucket for sure
        krsort($assignmentSizes);

        //create buckets
        $buckets = [];
        $bucketsNeeded = $bucketsCount - $distributedBuckets;
        for ($i = 0; $i < $bucketsNeeded; $i++) {
            $buckets[$i] = $bucketSize;
        }

        //distribute parties to buckets
        $bucketMembers = [];
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
                        $bucketMembers[$biggestRemainingIndex][$currentPartyId] = $currentPartSize;

                        //adapt bucket sizes
                        $buckets[$biggestRemainingIndex] -= $currentPartSize;

                        break;
                    } else {
                        //party does not fit fully into bucket, therefore we have to continue
                        $currentPartSize -= $biggestRemaining;

                        $bucketMembers[$biggestRemainingIndex][$currentPartyId] = $biggestRemaining;

                        //adapt bucket sizes
                        $buckets[$biggestRemainingIndex] = 0;
                    }
                }
            }
        }

        ### randomly assign a bucket to a containing member

        //shuffle array
        $bucketIds = array_keys($bucketMembers);
        shuffle($buckets);
        $step = $bucketSize / count($buckets);
        $currentStep = 0;
        foreach ($bucketIds as $bucketId) {
            $members = $bucketMembers[$bucketId];

            //sort by value size, so big parties are more likely to get assigned
            arsort($members);
            $memberIds = array_keys($members);

            //chose member inside threshold range
            $currentSize = 0;
            $chosenMember = $memberIds[0];
            foreach ($memberIds as $memberId) {
                if ($currentSize > $currentStep) {
                    //take member from last iteration
                    break;
                }

                $chosenMember = $memberId;
                $currentSize += $members[$memberId];
            }

            //preserve result
            $partyBucketAssignments[$chosenMember] += 1;

            //increase threshold
            $currentStep += $step;
        }

        return $partyBucketAssignments;
    }

    /**
     * tries to generate the events
     * returns true if successful.
     *
     * @param NodikaConfiguration $nodikaConfiguration
     * @param callable $memberAllowedCallable with arguments $startDateTime, $endDateTime, $member which returns a boolean if the event can happen
     *
     * @return NodikaOutput
     */
    public function generateNodika(NodikaConfiguration $nodikaConfiguration, $memberAllowedCallable)
    {
        $generationResult = new GenerationResult(null);
        $generationResult->generationDateTime = new \DateTime();

        $nodikaOutput = new NodikaOutput();
        $nodikaOutput->version = 1;

        $conflictCallable = $this->buildConflictBuffer($nodikaConfiguration);

        /* @var NMemberConfiguration[] $members */
        $members = [];
        foreach ($nodikaConfiguration->memberConfigurations as $memberConfiguration) {
            if ($memberConfiguration->isEnabled) {
                $members[$memberConfiguration->id] = $memberConfiguration;
            }
        }

        /* @var EventTypeConfiguration[] $eventTypeDistributions */
        $eventTypeDistributions = [];
        foreach ($nodikaConfiguration->memberEventTypeDistributions as $memberEventTypeDistribution) {
            $eventTypeDistributions[$memberEventTypeDistribution->newMemberConfiguration->id] = clone $memberEventTypeDistribution->eventTypeAssignment;
        }

        $totalEvents = 0;

        /* @var IdealQueueMember[] $idealQueueMembers */
        $idealQueueMembers = [];
        foreach ($members as $member) {
            $idealQueueMember = new IdealQueueMember();
            $idealQueueMember->id = $member->id;
            $idealQueueMember->totalWeekdayCount = $eventTypeDistributions[$member->id]->weekday;
            $idealQueueMember->totalSaturdayCount = $eventTypeDistributions[$member->id]->saturday;
            $idealQueueMember->totalSundayCount = $eventTypeDistributions[$member->id]->sunday;
            $idealQueueMember->totalHolidayCount = $eventTypeDistributions[$member->id]->holiday;
            $idealQueueMember->calculateTotalEventCount();
            $totalEvents += $idealQueueMember->totalEventCount;
            $idealQueueMembers[$idealQueueMember->id] = $idealQueueMember;
        }

        $idealQueue = (array)($nodikaConfiguration->beforeEvents);
        if (count($idealQueue) > $totalEvents) {
            //cut off too large beginning arrays
            $idealQueue = array_slice($idealQueue, $totalEvents);
        }

        foreach ($idealQueueMembers as $idealQueueMember) {
            foreach ($idealQueue as $item) {
                if ($item === $idealQueueMember->id) {
                    ++$idealQueueMember->totalEventCount;
                    ++$idealQueueMember->doneEventCount;
                }
            }
            $idealQueueMember->calculatePartDone();
        }

        //cut off beforeEvents again
        $idealQueue = [];
        while (true) {
            //find lowest part done
            $lowestPartDone = 1;
            $lowestIndex = 0;
            foreach ($idealQueueMembers as $key => $value) {
                if ($lowestPartDone > $value->partDone) {
                    $lowestPartDone = $value->partDone;
                    $lowestIndex = $key;
                }
            }

            if (1 === $lowestPartDone) {
                //all members have delivered all events
                break;
            }

            $myMember = $idealQueueMembers[$lowestIndex];

            $idealQueue[] = $myMember->id;
            ++$myMember->doneEventCount;
            $myMember->calculatePartDone();
        }

        //set available to correct value
        foreach ($idealQueueMembers as $idealQueueMember) {
            $idealQueueMember->setAllAvailable();
        }

        $holidays = [];
        foreach ($nodikaConfiguration->holidays as $holiday) {
            $holidays[(new \DateTime($holiday->format('d.m.Y')))->getTimestamp()] = 1;
        }

        //this must be equal!
        assert(count($idealQueue) === $totalEvents);

        $startDateTime = clone $nodikaConfiguration->startDateTime;
        $assignedEventCount = 0;
        $queueIndex = 0;
        while ($startDateTime < $nodikaConfiguration->endDateTime) {
            $day = new \DateTime($startDateTime->format('d.m.Y'));
            $endDate = clone $startDateTime;
            $endDate = $this->addInterval($endDate, $nodikaConfiguration);

            //create callable for each day type
            $fitsFunc = function ($memberId) use (&$startDateTime, &$endDate, &$assignedEventCount, &$members, &$memberAllowedCallable, &$conflictCallable) {
                $res =
                    $memberAllowedCallable($startDateTime, $endDate, $assignedEventCount, $members[$memberId]) &&
                    $conflictCallable($assignedEventCount, $members[$memberId]);

                return $res;
            };
            $advancedFitsFunc = null;
            $advancedFitSuccessful = null;
            if (isset($holidays[$day->getTimestamp()])) {
                $advancedFitsFunc = function (&$targetMember) use (&$fitsFunc) {
                    /* @var IdealQueueMember $targetMember */
                    $res = $targetMember->availableHolidayCount > 0 && $fitsFunc($targetMember->id);

                    return $res;
                };
                $advancedFitSuccessful = function (&$targetMember) use ($queueIndex) {
                    /* @var IdealQueueMember $targetMember */
                    $targetMember->assignHoliday($queueIndex);
                };
            } else {
                $dayOfWeek = $day->format('N');
                if (7 === $dayOfWeek) {
                    //sunday
                    $advancedFitsFunc = function (&$targetMember) use (&$fitsFunc) {
                        /* @var IdealQueueMember $targetMember */
                        $res = $targetMember->availableSundayCount > 0 && $fitsFunc($targetMember->id);

                        return $res;
                    };
                    $advancedFitSuccessful = function (&$targetMember) use ($queueIndex) {
                        /* @var IdealQueueMember $targetMember */
                        $targetMember->assignSunday($queueIndex);
                    };
                } elseif (6 === $dayOfWeek) {
                    //saturday
                    $advancedFitsFunc = function (&$targetMember) use (&$fitsFunc) {
                        /* @var IdealQueueMember $targetMember */
                        $res = $targetMember->availableSaturdayCount > 0 && $fitsFunc($targetMember->id);

                        return $res;
                    };
                    $advancedFitSuccessful = function (&$targetMember) use ($queueIndex) {
                        /* @var IdealQueueMember $targetMember */
                        $targetMember->assignSaturday($queueIndex);
                    };
                } else {
                    //weekday
                    $advancedFitsFunc = function (&$targetMember) use (&$fitsFunc) {
                        /* @var IdealQueueMember $targetMember */
                        $res = $targetMember->availableWeekdayCount > 0 && $fitsFunc($targetMember->id);

                        return $res;
                    };
                    $advancedFitSuccessful = function (&$targetMember) use ($queueIndex) {
                        /* @var IdealQueueMember $targetMember */
                        $targetMember->assignWeekday($queueIndex);
                    };
                }
            }

            $targetMember = $idealQueueMembers[$idealQueue[$queueIndex]];
            if ($advancedFitsFunc($targetMember)) {
                //the member fits yay; that was easy
                $advancedFitSuccessful($targetMember);
            } else {
                $assignmentFound = false;
                //the search begins; look n to the right, then continue with n+1
                //totalEvents as upper bound; this will not be reached probably
                for ($i = 1; $i < $totalEvents; ++$i) {
                    //n to right
                    $newIndex = $queueIndex + $i;
                    if ($newIndex < $totalEvents) {
                        $targetMember = $idealQueueMembers[$idealQueue[$newIndex]];
                        if ($advancedFitsFunc($targetMember)) {
                            //the member fits!
                            $advancedFitSuccessful($targetMember);
                            $assignmentFound = true;

                            //now correct the queue
                            //this is in the future; so no further corrections necessary
                            //we simply insert the new index at the required position
                            //get the id
                            $queueId = $idealQueue[$newIndex];
                            //remove from queue
                            unset($idealQueue[$newIndex]);
                            //reset keys
                            $idealQueue = array_values($idealQueue);
                            //insert id at new place
                            array_splice($idealQueue, $queueIndex, 0, $queueId);

                            break;
                        }
                    }

                    //n to left
                    /*
                     * skipped this implementation; problems:
                     *  - too complex
                     *  - unpredictable behaviour in large datasets (non termination!)
                     */
                    /*
                    $newIndex = $queueIndex - $i;
                    if ($newIndex >= 0) {
                        $newTargetMember = $idealQueueMembers[$idealQueue[$newIndex]];
                        if ($advancedFitsFunc($newTargetMember)) {
                            //the member fits!
                            //now correct the queue
                            //this is in the past! so we need to do corrections
                            //clear history
                            for ($j = $newIndex; $j < $queueIndex; $j++) {
                                $myTargetMember = $idealQueueMembers[$idealQueue[$newIndex]];
                                $myTargetMember->removeAssignments($newIndex);
                            }

                            //get the id
                            $queueId = $idealQueue[$newIndex];
                            //remove from queue
                            unset($idealQueue[$newIndex]);
                            //reset keys
                            $idealQueue = array_values($idealQueue);
                            //insert id at new place
                            array_splice($idealQueue, $queueIndex, 0, $queueId);

                            $queueIndex = $newIndex;
                            break;
                        }
                    }
                    */
                }
                if (!$assignmentFound) {
                    return $this->returnNodikaError($nodikaOutput, GenerationStatus::NO_ALLOWED_MEMBER_FOR_EVENT);
                }
            }

            ++$queueIndex;
            ++$assignedEventCount;
            $startDateTime = $this->addInterval($startDateTime, $nodikaConfiguration);

            if (!($queueIndex < $totalEvents)) {
                break;
            }
        }
    }

    private function constructEvents(EventGeneration $eventGeneration)
    {
        $now = new \DateTime();

        $startExpression = CronExpression::factory($eventGeneration->getStartCronExpression());
        $currentStartDate = $startExpression->getNextRunDate($eventGeneration->getStartDateTime(), 0, true, $now->getTimezone()->getName());

        $endExpression = CronExpression::factory($eventGeneration->getEndCronExpression());
        $currentEndDate = $endExpression->getNextRunDate($currentStartDate, 0, true, $now->getTimezone()->getName());

        /* @var Event[] $result */
        $result = [];
        while ($currentStartDate < $eventGeneration->getEndDateTime()) {
            $event = new Event();
            $event->setStartDateTime($currentStartDate);
            $event->setEndDateTime($currentEndDate);
            $event->setGeneratedBy($eventGeneration);
            $result[] = $event;

            $currentStartDate = $startExpression->getNextRunDate();
            $currentEndDate = $endExpression->getNextRunDate();
        }

        return $result;
    }

    /**
     * applies specified exceptions to algorithm
     *
     * @param EventGeneration $eventGeneration
     * @param Event[] $events
     */
    private function assignEventType(EventGeneration $eventGeneration, array $events)
    {
        foreach ($events as $event) {
            $dayOfWeek = $event->getStartDateTime()->format('N');
            if (7 === $dayOfWeek) {
                $event->setEventType(EventType::SUNDAYS);
            } elseif (6 === $dayOfWeek) {
                $event->setEventType(EventType::SATURDAY);
            } else {
                $event->setEventType(EventType::WEEKDAY);
            }
        }
    }

    /**
     * applies specified exceptions to algorithm
     *
     * @param EventGeneration $eventGeneration
     * @param Event[] $events
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
                    $event->setEventType($dateException->getEventType());
                }
            }

        }
    }

    private function getEventTargets(EventGeneration $eventGeneration)
    {
        $targets = [];
        $currentId = 1;
        foreach ($eventGeneration->getFrontendUsers() as $frontendUser) {
            $targets[] = EventTarget::fromFrontendUser($currentId++, $frontendUser);
        }
        foreach ($eventGeneration->getMembers() as $member) {
            $targets[] = EventTarget::fromMember($currentId++, $member);
        }
        return $targets;
    }

    /**
     * tries to generate the events
     * returns true if successful.
     *
     * @param EventGeneration $eventGeneration
     * @return Event[]
     */
    public function generate(EventGeneration $eventGeneration)
    {
        $events = $this->constructEvents($eventGeneration);
        $this->assignEventType($eventGeneration, $events);
        $this->processExceptions($eventGeneration, $events);

        $targets = $this->getEventTargets($eventGeneration);

        $queueGenerator = new QueueGenerator();

        return [];
    }
}
