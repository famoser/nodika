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
use AppBundle\Enum\RoundRobinStatusCode;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Model\EventLineGeneration\Base\BaseConfiguration;
use AppBundle\Model\EventLineGeneration\Base\BaseMemberConfiguration;
use AppBundle\Model\EventLineGeneration\GeneratedEvent;
use AppBundle\Model\EventLineGeneration\GenerationResult;
use AppBundle\Model\EventLineGeneration\Nodika\MemberConfiguration as NMemberConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaConfiguration;
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

        $eventTypeConfiguration = $nodikaConfiguration->eventTypeConfiguration;
        $totalPoints = $holidayCount * $eventTypeConfiguration->holiday;
        $totalPoints += $sundayCount * $eventTypeConfiguration->sunday;
        $totalPoints += $saturdayCount * $eventTypeConfiguration->saturday;
        $totalPoints += $weekdayCount * $eventTypeConfiguration->weekday;

        $totalMemberPoints = 0;
        foreach ($enabledMembers as $enabledMember) {
            $totalMemberPoints += $enabledMember->points;
        }

        $pointsPerMemberPoint = $totalPoints / $totalMemberPoints;


        return true;
    }
}