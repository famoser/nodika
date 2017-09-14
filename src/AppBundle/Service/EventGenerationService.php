<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 18:24
 */

namespace AppBundle\Service;


use AppBundle\Entity\Event;
use AppBundle\Entity\EventLineGeneration;
use AppBundle\Enum\EventGenerationServicePersistResponse;
use AppBundle\Enum\NodikaStatusCode;
use AppBundle\Enum\RoundRobinStatusCode;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Model\EventGenerationService\IdealQueueMember;
use AppBundle\Model\EventLineGeneration\Base\BaseConfiguration;
use AppBundle\Model\EventLineGeneration\Base\BaseMemberConfiguration;
use AppBundle\Model\EventLineGeneration\GeneratedEvent;
use AppBundle\Model\EventLineGeneration\GenerationResult;
use AppBundle\Model\EventLineGeneration\Nodika\EventTypeConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\MemberConfiguration as NMemberConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\MemberEventTypeDistribution;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaOutput;
use AppBundle\Model\EventLineGeneration\RoundRobin\MemberConfiguration as RRMemberConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinOutput;
use AppBundle\Service\Interfaces\EventGenerationServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class EventGenerationService implements EventGenerationServiceInterface
{
    /* @var RegistryInterface $doctrine */
    private $doctrine;

    /* @var TranslatorInterface $translator */
    private $translator;

    /* @var Session $session */
    private $session;

    /* the accuracy of double comparision at critical points */
    const ACCURACY_THRESHOLD = 0.0001;

    /* the random accuracy used. must be a valid input for random_int. */
    const RANDOM_ACCURACY = 1000;

    public function __construct($doctrine, $translator, $session)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->session = $session;
    }

    /**
     * @param $message
     */
    private function displayError($message)
    {
        $this->session->getFlashBag()->set(StaticMessageHelper::FLASH_ERROR, $message);
    }

    /**
     * @param $message
     */
    private function displaySuccess($message)
    {
        $this->session->getFlashBag()->set(StaticMessageHelper::FLASH_SUCCESS, $message);
    }

    /**
     * @param RoundRobinOutput $roundRobinResult
     * @param int $status
     * @return RoundRobinOutput
     */
    private function returnRoundRobinError(RoundRobinOutput $roundRobinResult, $status)
    {
        $this->displayError(
            $this->translator->trans(
                RoundRobinStatusCode::getTranslation($status),
                [],
                RoundRobinStatusCode::getTranslationDomainStatic()
            )
        );

        $roundRobinResult->roundRobinStatusCode = $status;
        return $roundRobinResult;
    }

    /**
     * @param NodikaOutput $nodikaOutput
     * @param int $status
     * @return NodikaOutput
     */
    private function returnNodikaError(NodikaOutput $nodikaOutput, $status)
    {
        $this->displayError(
            $this->translator->trans(
                NodikaStatusCode::getTranslation($status),
                [],
                NodikaStatusCode::getTranslationDomainStatic()
            )
        );

        $nodikaOutput->nodikaStatusCode = $status;
        return $nodikaOutput;
    }

    /**
     * @param RoundRobinOutput $roundRobinResult
     * @return RoundRobinOutput
     */
    private function returnRoundRobinSuccess(RoundRobinOutput $roundRobinResult)
    {
        $status = RoundRobinStatusCode::SUCCESSFUL;
        $this->displaySuccess(
            $this->translator->trans(
                RoundRobinStatusCode::getTranslation($status),
                [],
                RoundRobinStatusCode::getTranslationDomainStatic()
            )
        );

        $roundRobinResult->roundRobinStatusCode = $status;
        return $roundRobinResult;
    }

    /**
     * @param NodikaOutput $nodikaOutput
     * @return NodikaOutput
     */
    private function returnNodikaSuccess(NodikaOutput $nodikaOutput)
    {
        $status = NodikaStatusCode::SUCCESSFUL;
        $this->displaySuccess(
            $this->translator->trans(
                NodikaStatusCode::getTranslation($status),
                [],
                NodikaStatusCode::getTranslationDomainStatic()
            )
        );

        $nodikaOutput->nodikaStatusCode = $status;
        return $nodikaOutput;
    }

    /**
     * @param BaseConfiguration $configuration
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
                    $myArr["start"] = $eventEntry->startDateTime->getTimestamp() - $conflictPufferInSeconds;
                    $myArr["end"] = $eventEntry->endDateTime->getTimestamp() + $conflictPufferInSeconds;
                    $myArr["id"] = $eventEntry->memberId;
                    $eventLineEvents[$eventEntry->startDateTime->getTimestamp()][] = $myArr;
                }
                ksort($eventLineEvents);
                $allEventLineEvents[] = call_user_func_array('array_merge', $eventLineEvents);;
            }
        }

        $eventLineCount = count($allEventLineEvents);
        $activeIndexes = [];
        $eventLineCounts = [];
        for ($i = 0; $i < $eventLineCount; $i++) {
            $activeIndexes[$i] = 0;
            $eventLineCounts[$i] = count($allEventLineEvents[$i]);
        }

        $conflictBuffer = [];
        $assignedEventCount = 0;

        $currentDate = clone($configuration->startDateTime);
        $dateIntervalAdd = "PT" . $configuration->lengthInHours . "H";
        while ($currentDate < $configuration->endDateTime) {
            $endDate = clone($currentDate);
            $endDate->add(new \DateInterval($dateIntervalAdd));
            $startTimeStamp = $currentDate->getTimestamp();
            $endTimeStamp = $endDate->getTimestamp();
            $currentConflictBuffer = [];
            for ($i = 0; $i < $eventLineCount; $i++) {
                for ($j = $activeIndexes[$i]; $j < $eventLineCounts[$i]; $j++) {
                    $currentEvent = $allEventLineEvents[$i][$j];
                    if ($currentEvent["end"] < $startTimeStamp) {
                        //not in critical zone yet
                        $activeIndexes[$i]++;
                    } else {
                        if ($currentEvent["start"] <= $startTimeStamp && $currentEvent["end"] >= $startTimeStamp) {
                            //start overlap
                            $currentConflictBuffer[] = $currentEvent["id"];
                        } else if ($currentEvent["end"] <= $endTimeStamp && $currentEvent["end"] >= $endTimeStamp) {
                            //end overlap
                            $currentConflictBuffer[] = $currentEvent["id"];
                        } else {
                            //no overlap anymore
                            break;
                        }
                    }
                }
            }

            $conflictBuffer[$assignedEventCount] = $currentConflictBuffer;
            $assignedEventCount++;
            $currentDate = $endDate;
        }

        return function ($currentEventCount, $member) use ($conflictBuffer) {
            /* @var BaseMemberConfiguration $member */
            return in_array($member->id, $conflictBuffer[$currentEventCount]);
        };
    }

    /**
     * tries to generate the events
     * returns true if successful
     *
     * @param RoundRobinConfiguration $roundRobinConfiguration
     * @param callable $memberAllowedCallable with arguments $startDateTime, $endDateTime, $member which returns a boolean if the event can happen
     * @return RoundRobinOutput
     * @throws \Exception
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
        $currentDate = clone($roundRobinConfiguration->startDateTime);
        $dateIntervalAdd = "PT" . $roundRobinConfiguration->lengthInHours . "H";
        while ($currentDate < $roundRobinConfiguration->endDateTime) {
            $endDate = clone($currentDate);
            $endDate->add(new \DateInterval($dateIntervalAdd));
            //check if something in priority queue
            /* @var RRMemberConfiguration $matchMember */
            $matchMember = null;
            if (count($priorityQueue) > 0) {
                $i = 0;
                for (; $i < count($priorityQueue); $i++) {
                    if (
                        $memberAllowedCallable($currentDate, $endDate, $assignedEventCount, $priorityQueue[$i]) &&
                        $conflictCallable($assignedEventCount, $priorityQueue[$i])
                    ) {
                        $matchMember = $priorityQueue[$i];
                        break;
                    }
                }
                if ($matchMember != null) {
                    unset($priorityQueue[$i]);
                    //reset keys in array (0,1,2,3,4,...)
                    $priorityQueue = array_values($priorityQueue);
                }
            }
            if ($matchMember == null) {
                $startIndex = $activeIndex;
                while (true) {
                    //wrap around index
                    if ($activeIndex >= $totalMembers) {
                        $activeIndex = 0;
                    }

                    $myMember = $members[$activeIndex];
                    if ($memberAllowedCallable($currentDate, $endDate, $assignedEventCount, $myMember) &&
                        $conflictCallable($assignedEventCount, $myMember)) {
                        $matchMember = $myMember;
                        $activeIndex++;
                        break;
                    } else {
                        $priorityQueue[] = $myMember;
                        $activeIndex++;
                        if ($startIndex == $activeIndex) {
                            return $this->returnRoundRobinError($roundRobinResult, RoundRobinStatusCode::NO_MATCHING_MEMBER);
                        }
                    }
                }
            }

            if ($matchMember == null) {
                throw new \Exception("cannot happen!");
            } else {
                $event = new GeneratedEvent();
                $event->memberId = $matchMember->id;
                $event->startDateTime = $currentDate;
                $event->endDateTime = $endDate;
                $generationResult->events[] = $event;
                $assignedEventCount++;
            }
            $currentDate = clone($endDate);
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
     * persist the events associated with this generation in the database
     *
     * @param EventLineGeneration $generation
     * @return bool
     */
    public function persist(EventLineGeneration $generation, GenerationResult $generationResult)
    {
        $memberById = [];
        foreach ($this->doctrine->getRepository("AppBundle:Member")->findBy(["organisation" => $generation->getEventLine()->getOrganisation()->getId()]) as $item) {
            $memberById[$item->getId()] = $item;
        }
        $em = $this->doctrine->getManager();
        foreach ($generationResult->events as $event) {
            if (isset($memberById[$event->memberId])) {
                $newEvent = new Event();
                $newEvent->setStartDateTime($event->startDateTime);
                $newEvent->setEndDateTime($event->endDateTime);
                $newEvent->setEventLine($generation->getEventLine());
                $newEvent->setMember($memberById[$event->memberId]);
                $em->persist($newEvent);
            } else {
                $this->displayError(
                    $this->translator->trans(
                        "member_not_found_anymore",
                        [],
                        "enum_event_generation_service_persist_response"
                    )
                );
                return EventGenerationServicePersistResponse::MEMBER_NOT_FOUND_ANYMORE;
            }
        }
        $em->flush();
        return EventGenerationServicePersistResponse::SUCCESSFUL;
    }

    /**
     * @param NodikaConfiguration $nodikaConfiguration
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
            $holidays[(new \DateTime($holiday->format("d.m.Y")))->getTimestamp()] = 1;
        }

        $currentDate = clone($nodikaConfiguration->startDateTime);
        $dateIntervalAdd = "PT" . $nodikaConfiguration->lengthInHours . "H";
        $oneMore = 1;
        while ($currentDate < $nodikaConfiguration->endDateTime || $oneMore--) {
            $day = new \DateTime($currentDate->format("d.m.Y"));
            if (isset($holidays[$day->getTimestamp()])) {
                $holidayCount++;
            } else {
                $dayOfWeek = $day->format('N');
                if ($dayOfWeek == 7) {
                    $sundayCount++;
                } else if ($dayOfWeek == 6) {
                    $saturdayCount++;
                } else {
                    $weekdayCount++;
                }
            }

            $currentDate->add(new \DateInterval($dateIntervalAdd));
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
        foreach ($nodikaConfiguration->memberConfigurations as $memberConfiguration) {
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
            $member = clone($enabledMember);
            $member->endScore = $partiesArray[$enabledMember->id];
            $member->luckyScore = $this->convertToLuckyScore($totalPoints, $member->points);
            $memberEventTypeDistribution->newMemberConfiguration = $enabledMember;

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
     * creates a lucky score
     *
     * @param $totalPoints
     * @param $reachedPoints
     * @return double
     */
    private function convertToLuckyScore($totalPoints, $reachedPoints)
    {
        return ($reachedPoints / $totalPoints) * 100.0;
    }

    /**
     * creates a lucky score
     *
     * @param double $totalPoints
     * @param double $luckyScore
     * @return int
     */
    private function convertFromLuckyScore($totalPoints, $luckyScore)
    {
        $realScore = $luckyScore / 100.0;
        return $totalPoints * $realScore;
    }

    /**
     * distribute the days to the members
     *
     * @param array $partiesArray is an array of the form (int => double) (target points per member)
     * @param array $distributedDaysArray is an array of the form (int => (int => int)) (distributed dayKey => dayCount per member)
     * @param double $dayValue the value of this day for $distributedPointsArray calculation
     * @param int $dayCount the amount of
     * @param int $dayKey the key of this day used in $distributedDaysArray
     */
    private function distributeDays(&$partiesArray, &$distributedDaysArray, $dayValue, $dayCount, $dayKey)
    {
        //get holiday assignment
        $bucketAssignment = $this->bucketsAlgorithm($partiesArray, $dayCount);
        //add points to $distributedPointsArray
        foreach ($bucketAssignment as $bucketId => $memberId) {
            $partiesArray[$memberId] -= $dayValue;
            $distributedDaysArray[$memberId][$dayKey] += 1;
        }
    }

    /**
     * the buckets algorithm puts the parties into equal size buckets
     * according to the share of one party into the bucket it secures that bucket with that probability
     * the parties are distributed to the buckets to be in as few buckets as possible
     *
     * @param array $parties is an array of the form (int => double)
     * @param int $bucketsCount the number of buckets to distribute
     * @return array is an array of the form (int => int)
     */
    private function bucketsAlgorithm($parties, $bucketsCount)
    {
        //prepare parties (and sort by size)
        $sizes = [];
        $totalSize = 0;
        foreach ($parties as $partyId => $partySize) {
            $sizes[$partySize][] = $partyId;
            $totalSize += $partySize;
        }
        ksort($sizes);

        //prepare buckets
        $bucketSize = $totalSize / $bucketsCount;
        $remainingBucketSizes = [];
        $bucketMembers = [];
        for ($i = 0; $i < $bucketsCount; $i++) {
            $remainingBucketSizes[$i] = $bucketSize;
            $bucketMembers[$i] = [];
        }

        //distribute parties to buckets
        foreach ($sizes as $partySize => $parties) {
            foreach ($parties as $party) {
                $myPartSize = $partySize;
                while ($myPartSize > static::ACCURACY_THRESHOLD) {
                    //find biggest remaining bucket
                    $biggestRemaining = 0;
                    $biggestRemainingIndex = 0;
                    for ($i = 0; $i < $bucketsCount; $i++) {
                        if ($biggestRemaining < $remainingBucketSizes[$i]) {
                            $biggestRemainingIndex = $i;
                            $biggestRemaining = $remainingBucketSizes[$i];
                        }
                    }

                    if ($biggestRemaining > $myPartSize) {
                        $biggestRemaining -= $myPartSize;
                        $remainingBucketSizes[$biggestRemainingIndex] = $biggestRemaining;
                        $myPartSize = 0;
                    } else {
                        $myPartSize -= $biggestRemaining;
                        $remainingBucketSizes[$biggestRemainingIndex] = 0;
                    }
                    $bucketMembers[$biggestRemainingIndex][$party] = $biggestRemaining;
                }
            }
        }

        //choose winner per bucket
        $winnerPerBucket = [];
        foreach ($bucketMembers as $bucketIndex => $members) {
            if (count($members) == 1) {
                //fully filled out!
                reset($members);
                $winnerPerBucket[$bucketIndex] = key($members);
            }
            //we need to choose randomly!
            $randomValue = random_int(0, static::RANDOM_ACCURACY);
            asort($members);
            foreach ($members as $memberId => $memberPart) {
                $bucketPercentage = $memberPart / $bucketSize;
                //convert to int on RANDOM_ACCURACY scala. +0.5 because of rounding
                $memberValue = (int)($bucketPercentage * static::RANDOM_ACCURACY + 0.5);

                //set this as the winner so we have a match for sure
                $winnerPerBucket[$bucketIndex] = $memberId;

                //smaller/equal cause it starts at 0
                if ($memberValue <= $randomValue) {
                    break;
                }
            }
        }
        return $winnerPerBucket;
    }

    /**
     * tries to generate the events
     * returns true if successful
     *
     * @param NodikaConfiguration $nodikaConfiguration
     * @param callable $memberAllowedCallable with arguments $startDateTime, $endDateTime, $member which returns a boolean if the event can happen
     * @return NodikaOutput
     * @throws \Exception
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
            $eventTypeDistributions[$memberEventTypeDistribution->newMemberConfiguration->id] = clone($memberEventTypeDistribution->eventTypeAssignment);
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

        $idealQueue = clone($nodikaConfiguration->beforeEvents);
        if (count($idealQueue) > $totalEvents) {
            //cut off too large beginning arrays
            $idealQueue = array_slice($idealQueue, $totalEvents);
        }

        foreach ($idealQueueMembers as $idealQueueMember) {
            foreach ($idealQueue as $item) {
                if ($item == $idealQueueMember->id) {
                    $idealQueueMember->totalEventCount++;
                    $idealQueueMember->doneEventCount++;
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

            if ($lowestPartDone == 1) {
                //all members have delivered all events
                break;
            }

            $myMember = $idealQueueMembers[$lowestIndex];

            $idealQueue[] = $myMember->id;
            $myMember->doneEventCount++;
            $myMember->calculatePartDone();
        }

        $holidays = [];
        foreach ($nodikaConfiguration->holidays as $holiday) {
            $holidays[(new \DateTime($holiday->format("d.m.Y")))->getTimestamp()] = 1;
        }

        //this must be equal!
        assert(count($idealQueue) == $totalEvents);

        $startDateTime = clone($nodikaConfiguration->startDateTime);
        $dateIntervalAdd = "PT" . $nodikaConfiguration->lengthInHours . "H";
        $assignedEventCount = 0;
        $queueIndex = 0;
        while ($startDateTime < $nodikaConfiguration->endDateTime) {
            $day = new \DateTime($startDateTime->format("d.m.Y"));
            $endDate = clone($startDateTime);
            $endDate->add(new \DateInterval($dateIntervalAdd));


            //create callable for each day type
            $fitsFunc = function ($memberId) use (&$startDateTime, &$endDate, &$assignedEventCount, &$members, &$memberAllowedCallable, &$conflictCallable) {
                return
                    $memberAllowedCallable($startDateTime, $endDate, $assignedEventCount, $members[$memberId]) &&
                    $conflictCallable($assignedEventCount, $members[$memberId]);
            };
            $advancedFitsFunc = null;
            $advancedFitSuccessful = null;
            if (isset($holidays[$day->getTimestamp()])) {
                $advancedFitsFunc = function (&$targetMember) use (&$fitsFunc) {
                    /* @var IdealQueueMember $targetMember */
                    return $targetMember->availableHolidayCount > 0 && $fitsFunc($targetMember->id);
                };
                $advancedFitSuccessful = function (&$targetMember) use ($queueIndex) {
                    /* @var IdealQueueMember $targetMember */
                    $targetMember->assignHoliday($queueIndex);
                };
            } else {
                $dayOfWeek = $day->format('N');
                if ($dayOfWeek == 7) {
                    //sunday
                    $advancedFitsFunc = function (&$targetMember) use (&$fitsFunc) {
                        /* @var IdealQueueMember $targetMember */
                        return $targetMember->availableSundayCount > 0 && $fitsFunc($targetMember->id);
                    };
                    $advancedFitSuccessful = function (&$targetMember) use ($queueIndex) {
                        /* @var IdealQueueMember $targetMember */
                        $targetMember->assignSunday($queueIndex);
                    };
                } else if ($dayOfWeek == 6) {
                    //saturday
                    $advancedFitsFunc = function (&$targetMember) use (&$fitsFunc) {
                        /* @var IdealQueueMember $targetMember */
                        return $targetMember->availableSaturdayCount > 0 && $fitsFunc($targetMember->id);
                    };
                    $advancedFitSuccessful = function (&$targetMember) use ($queueIndex) {
                        /* @var IdealQueueMember $targetMember */
                        $targetMember->assignSaturday($queueIndex);
                    };
                } else {
                    //weekday
                    $advancedFitsFunc = function (&$targetMember) use (&$fitsFunc) {
                        /* @var IdealQueueMember $targetMember */
                        return $targetMember->availableWeekdayCount > 0 && $fitsFunc($targetMember->id);
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
                //the search begins; look n to the right, then n to the left, then continue with n+1
                //totalEvents as upper bound; this will not be reached probably
                for ($i = 0; $i < $totalEvents; $i++) {
                    //n to right
                    $newIndex = $queueIndex + $i;
                    if ($newIndex < $totalEvents) {
                        $targetMember = $idealQueueMembers[$idealQueue[$newIndex]];
                        if ($advancedFitsFunc($targetMember)) {
                            //the member fits!
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
            }

            $queueIndex++;
            $assignedEventCount++;
            $startDateTime->add(new \DateInterval($dateIntervalAdd));
        }


        $startDateTime = clone($nodikaConfiguration->startDateTime);
        $dateIntervalAdd = "PT" . $nodikaConfiguration->lengthInHours . "H";
        $assignedEventCount = 0;
        $queueIndex = 0;
        while ($startDateTime < $nodikaConfiguration->endDateTime) {
            $endDate = clone($startDateTime);
            $endDate->add(new \DateInterval($dateIntervalAdd));

            $targetMember = $idealQueueMembers[$idealQueue[$queueIndex]];

            $event = new GeneratedEvent();
            $event->memberId = $targetMember->id;
            $event->startDateTime = $startDateTime;
            $event->endDateTime = $endDate;
            $generationResult->events[] = $event;

            $queueIndex++;
            $assignedEventCount++;
            $startDateTime->add(new \DateInterval($dateIntervalAdd));
        }

        //prepare RR result
        $nodikaOutput->endDateTime = $startDateTime;
        $nodikaOutput->lengthInHours = $nodikaConfiguration->lengthInHours;
        $nodikaOutput->memberConfiguration = $members;
        $nodikaOutput->beforeEvents = array_merge(clone($nodikaConfiguration->beforeEvents), $idealQueue);
        $nodikaOutput->generationResult = $generationResult;
        return $this->returnNodikaSuccess($nodikaOutput);
    }
}