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

class OldServiceReference
{
    /**
     * tries to generate the events
     * returns true if successful.
     *
     * @param RoundRobinConfiguration $roundRobinConfiguration
     * @param callable                $clinicAllowedCallable   with arguments $startDateTime, $endDateTime, $clinic which returns a boolean if the event can happen
     *
     * @return RoundRobinOutput
     */
    public function generateRoundRobin(RoundRobinConfiguration $roundRobinConfiguration, $clinicAllowedCallable)
    {
        $generationResult = new GenerationResult(null);
        $generationResult->generationDateTime = new \DateTime();

        $roundRobinResult = new RoundRobinOutput();
        $roundRobinResult->version = 1;

        $conflictCallable = $this->buildConflictBuffer($roundRobinConfiguration);

        /* @var RRClinicConfiguration[] $clinics */
        $clinics = [];
        foreach ($roundRobinConfiguration->clinicConfigurations as $clinicConfiguration) {
            if ($clinicConfiguration->isEnabled) {
                $clinics[$clinicConfiguration->order] = $clinicConfiguration;
            }
        }
        //sorts by key
        ksort($clinics);
        $clinics = array_values($clinics);

        $assignedEventCount = 0;
        $activeIndex = 0;
        $totalClinics = \count($clinics);
        /* @var RRClinicConfiguration[] $priorityQueue */
        $priorityQueue = [];
        $currentDate = clone $roundRobinConfiguration->startDateTime;
        while ($currentDate < $roundRobinConfiguration->endDateTime) {
            $endDate = clone $currentDate;
            $endDate = $this->addInterval($endDate, $roundRobinConfiguration);
            //check if something in priority queue
            /* @var RRClinicConfiguration $matchClinic */
            $matchClinic = null;
            if (\count($priorityQueue) > 0) {
                $i = 0;
                for (; $i < \count($priorityQueue); ++$i) {
                    if (
                        $clinicAllowedCallable($currentDate, $endDate, $assignedEventCount, $priorityQueue[$i]) &&
                        $conflictCallable($assignedEventCount, $priorityQueue[$i])
                    ) {
                        $matchClinic = $priorityQueue[$i];
                        break;
                    }
                }
                if (null !== $matchClinic) {
                    unset($priorityQueue[$i]);
                    //reset keys in array (0,1,2,3,4,...)
                    $priorityQueue = array_values($priorityQueue);
                }
            }
            if (null === $matchClinic) {
                $startIndex = $activeIndex;
                while (true) {
                    $myClinic = $clinics[$activeIndex];
                    if ($clinicAllowedCallable($currentDate, $endDate, $assignedEventCount, $myClinic) &&
                        $conflictCallable($assignedEventCount, $myClinic)) {
                        $matchClinic = $myClinic;
                        ++$activeIndex;
                        break;
                    }
                    $priorityQueue[] = $myClinic;
                    ++$activeIndex;
                    //wrap around index
                    if ($activeIndex >= $totalClinics) {
                        $activeIndex = 0;
                    }
                    if ($startIndex === $activeIndex) {
                        return $this->returnRoundRobinError($roundRobinResult, RoundRobinStatusCode::NO_MATCHING_CLINIC);
                    }
                }
                //wrap around index
                if ($activeIndex >= $totalClinics) {
                    $activeIndex = 0;
                }
            }

            if (null === $matchClinic) {
                return $this->returnRoundRobinError($roundRobinResult, RoundRobinStatusCode::NO_MATCHING_CLINIC_2);
            }
            $event = new GeneratedEvent();
            $event->clinicId = $matchClinic->id;
            $event->startDateTime = $currentDate;
            $event->endDateTime = $endDate;
            $generationResult->events[] = $event;
            ++$assignedEventCount;

            $currentDate = clone $endDate;
        }

        //prepare RR result
        $roundRobinResult->endDateTime = $currentDate;
        $roundRobinResult->lengthInHours = $roundRobinConfiguration->lengthInHours;
        $roundRobinResult->clinicConfiguration = $clinics;
        $roundRobinResult->priorityQueue = $priorityQueue;
        $roundRobinResult->activeIndex = $activeIndex;
        $roundRobinResult->generationResult = $generationResult;

        return $this->returnRoundRobinSuccess($roundRobinResult);
    }

    /**
     * @param BaseConfiguration $configuration
     *
     * @return \Closure with arguments ($currentEventCount, $clinicId)
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
                    $myArr['id'] = $eventEntry->clinicId;
                    $eventLineEvents[$eventEntry->startDateTime->getTimestamp()][] = $myArr;
                }
                ksort($eventLineEvents);
                $collapsedArray = \call_user_func_array('array_merge', $eventLineEvents);
                $allEventLineEvents[] = $collapsedArray;
            }
        }

        $eventLineCount = \count($allEventLineEvents);
        $activeIndexes = [];
        $eventLineCounts = [];
        for ($i = 0; $i < $eventLineCount; ++$i) {
            $activeIndexes[$i] = 0;
            $eventLineCounts[$i] = \count($allEventLineEvents[$i]);
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

        $myFunc = function ($currentEventCount, $clinic) use ($conflictBuffer) {
            /* @var BaseClinicConfiguration $clinic */
            return !\in_array($clinic->id, $conflictBuffer[$currentEventCount], true);
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
            $daysAddInterval = new \DateInterval('P'.$days.'D');
            $dateTime->add($daysAddInterval);
            $hoursRemoveInterval = new \DateInterval('PT'.$hours.'H');
            $dateTime->sub($hoursRemoveInterval);
        } else {
            if ($days > 0) {
                $daysAddInterval = new \DateInterval('P'.$days.'D');
                $dateTime->add($daysAddInterval);
            }
            if ($hours > 0) {
                $hoursAddInterval = new \DateInterval('PT'.$hours.'H');
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
        /* @var NClinicConfiguration[] $enabledClinics */
        $enabledClinics = [];
        foreach ($nodikaConfiguration->clinicConfigurations as $clinicConfiguration) {
            if ($clinicConfiguration->isEnabled) {
                $enabledClinics[] = $clinicConfiguration;
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

        $totalClinicPoints = 0;
        foreach ($enabledClinics as $enabledClinic) {
            $totalClinicPoints += $enabledClinic->points;
        }

        $pointsPerClinicPoint = $totalPoints / $totalClinicPoints;

        //initialize partiesArray to distribute days with the bucket algorithm
        $partiesArray = [];
        $distributedDaysArray = [];
        foreach ($enabledClinics as $clinicConfiguration) {
            $partiesArray[$clinicConfiguration->id] = $pointsPerClinicPoint * $clinicConfiguration->points;
            $partiesArray[$clinicConfiguration->id] += $this->convertFromLuckyScore($totalPoints, $clinicConfiguration->luckyScore);
            $distributedDaysArray[$clinicConfiguration->id] = [];
            $distributedDaysArray[$clinicConfiguration->id][0] = 0;
            $distributedDaysArray[$clinicConfiguration->id][1] = 0;
            $distributedDaysArray[$clinicConfiguration->id][2] = 0;
            $distributedDaysArray[$clinicConfiguration->id][3] = 0;
        }

        //distribute days to parties
        $this->distributeDays($partiesArray, $distributedDaysArray, $eventTypeAssignment->holiday, $holidayCount, 3);
        $this->distributeDays($partiesArray, $distributedDaysArray, $eventTypeAssignment->sunday, $sundayCount, 2);
        $this->distributeDays($partiesArray, $distributedDaysArray, $eventTypeAssignment->saturday, $saturdayCount, 1);
        $this->distributeDays($partiesArray, $distributedDaysArray, $eventTypeAssignment->weekday, $weekdayCount, 0);

        //create configurations
        $nodikaConfiguration->clinicEventTypeDistributions = [];
        foreach ($enabledClinics as $enabledClinic) {
            $clinicEventTypeDistribution = new ClinicEventTypeDistribution(null);
            $clinic = clone $enabledClinic;
            $clinic->endScore = round($partiesArray[$enabledClinic->id], 2);
            $clinic->luckyScore = round($this->convertToLuckyScore($totalPoints, $clinic->points), 2);
            $clinicEventTypeDistribution->newClinicConfiguration = $clinic;

            $eventTypeAssignment = new EventTypeConfiguration(null);
            $eventTypeAssignment->holiday = $distributedDaysArray[$enabledClinic->id][3];
            $eventTypeAssignment->sunday = $distributedDaysArray[$enabledClinic->id][2];
            $eventTypeAssignment->saturday = $distributedDaysArray[$enabledClinic->id][1];
            $eventTypeAssignment->weekday = $distributedDaysArray[$enabledClinic->id][0];
            $clinicEventTypeDistribution->eventTypeAssignment = $eventTypeAssignment;

            $nodikaConfiguration->clinicEventTypeDistributions[] = $clinicEventTypeDistribution;
        }

        return true;
    }

    /**
     * tries to generate the events
     * returns true if successful.
     *
     * @param NodikaConfiguration $nodikaConfiguration
     * @param callable            $clinicAllowedCallable with arguments $startDateTime, $endDateTime, $clinic which returns a boolean if the event can happen
     *
     * @return NodikaOutput
     */
    public function generateNodika(NodikaConfiguration $nodikaConfiguration, $clinicAllowedCallable)
    {
        $generationResult = new GenerationResult(null);
        $generationResult->generationDateTime = new \DateTime();

        $nodikaOutput = new NodikaOutput();
        $nodikaOutput->version = 1;

        $conflictCallable = $this->buildConflictBuffer($nodikaConfiguration);

        /* @var NClinicConfiguration[] $clinics */
        $clinics = [];
        foreach ($nodikaConfiguration->clinicConfigurations as $clinicConfiguration) {
            if ($clinicConfiguration->isEnabled) {
                $clinics[$clinicConfiguration->id] = $clinicConfiguration;
            }
        }

        /* @var EventTypeConfiguration[] $eventTypeDistributions */
        $eventTypeDistributions = [];
        foreach ($nodikaConfiguration->clinicEventTypeDistributions as $clinicEventTypeDistribution) {
            $eventTypeDistributions[$clinicEventTypeDistribution->newClinicConfiguration->id] = clone $clinicEventTypeDistribution->eventTypeAssignment;
        }

        $totalEvents = 0;

        /* @var IdealQueueClinic[] $idealQueueClinics */
        $idealQueueClinics = [];
        foreach ($clinics as $clinic) {
            $idealQueueClinic = new IdealQueueClinic();
            $idealQueueClinic->id = $clinic->id;
            $idealQueueClinic->totalWeekdayCount = $eventTypeDistributions[$clinic->id]->weekday;
            $idealQueueClinic->totalSaturdayCount = $eventTypeDistributions[$clinic->id]->saturday;
            $idealQueueClinic->totalSundayCount = $eventTypeDistributions[$clinic->id]->sunday;
            $idealQueueClinic->totalHolidayCount = $eventTypeDistributions[$clinic->id]->holiday;
            $idealQueueClinic->calculateTotalEventCount();
            $totalEvents += $idealQueueClinic->totalEventCount;
            $idealQueueClinics[$idealQueueClinic->id] = $idealQueueClinic;
        }

        $idealQueue = (array) ($nodikaConfiguration->beforeEvents);
        if (\count($idealQueue) > $totalEvents) {
            //cut off too large beginning arrays
            $idealQueue = \array_slice($idealQueue, $totalEvents);
        }

        foreach ($idealQueueClinics as $idealQueueClinic) {
            foreach ($idealQueue as $item) {
                if ($item === $idealQueueClinic->id) {
                    ++$idealQueueClinic->totalEventCount;
                    ++$idealQueueClinic->doneEventCount;
                }
            }
            $idealQueueClinic->calculatePartDone();
        }

        //cut off beforeEvents again
        $idealQueue = [];
        while (true) {
            //find lowest part done
            $lowestPartDone = 1;
            $lowestIndex = 0;
            foreach ($idealQueueClinics as $key => $value) {
                if ($lowestPartDone > $value->partDone) {
                    $lowestPartDone = $value->partDone;
                    $lowestIndex = $key;
                }
            }

            if (1 === $lowestPartDone) {
                //all clinics have delivered all events
                break;
            }

            $myClinic = $idealQueueClinics[$lowestIndex];

            $idealQueue[] = $myClinic->id;
            ++$myClinic->doneEventCount;
            $myClinic->calculatePartDone();
        }

        //set available to correct value
        foreach ($idealQueueClinics as $idealQueueClinic) {
            $idealQueueClinic->setAllAvailable();
        }

        $holidays = [];
        foreach ($nodikaConfiguration->holidays as $holiday) {
            $holidays[(new \DateTime($holiday->format('d.m.Y')))->getTimestamp()] = 1;
        }

        //this must be equal!
        \assert(\count($idealQueue) === $totalEvents);

        $startDateTime = clone $nodikaConfiguration->startDateTime;
        $assignedEventCount = 0;
        $queueIndex = 0;
        while ($startDateTime < $nodikaConfiguration->endDateTime) {
            $day = new \DateTime($startDateTime->format('d.m.Y'));
            $endDate = clone $startDateTime;
            $endDate = $this->addInterval($endDate, $nodikaConfiguration);

            //create callable for each day type
            $fitsFunc = function ($clinicId) use (&$startDateTime, &$endDate, &$assignedEventCount, &$clinics, &$clinicAllowedCallable, &$conflictCallable) {
                $res =
                    $clinicAllowedCallable($startDateTime, $endDate, $assignedEventCount, $clinics[$clinicId]) &&
                    $conflictCallable($assignedEventCount, $clinics[$clinicId]);

                return $res;
            };
            $advancedFitsFunc = null;
            $advancedFitSuccessful = null;
            if (isset($holidays[$day->getTimestamp()])) {
                $advancedFitsFunc = function (&$targetClinic) use (&$fitsFunc) {
                    /* @var IdealQueueClinic $targetClinic */
                    $res = $targetClinic->availableHolidayCount > 0 && $fitsFunc($targetClinic->id);

                    return $res;
                };
                $advancedFitSuccessful = function (&$targetClinic) use ($queueIndex) {
                    /* @var IdealQueueClinic $targetClinic */
                    $targetClinic->assignHoliday($queueIndex);
                };
            } else {
                $dayOfWeek = $day->format('N');
                if (7 === $dayOfWeek) {
                    //sunday
                    $advancedFitsFunc = function (&$targetClinic) use (&$fitsFunc) {
                        /* @var IdealQueueClinic $targetClinic */
                        $res = $targetClinic->availableSundayCount > 0 && $fitsFunc($targetClinic->id);

                        return $res;
                    };
                    $advancedFitSuccessful = function (&$targetClinic) use ($queueIndex) {
                        /* @var IdealQueueClinic $targetClinic */
                        $targetClinic->assignSunday($queueIndex);
                    };
                } elseif (6 === $dayOfWeek) {
                    //saturday
                    $advancedFitsFunc = function (&$targetClinic) use (&$fitsFunc) {
                        /* @var IdealQueueClinic $targetClinic */
                        $res = $targetClinic->availableSaturdayCount > 0 && $fitsFunc($targetClinic->id);

                        return $res;
                    };
                    $advancedFitSuccessful = function (&$targetClinic) use ($queueIndex) {
                        /* @var IdealQueueClinic $targetClinic */
                        $targetClinic->assignSaturday($queueIndex);
                    };
                } else {
                    //weekday
                    $advancedFitsFunc = function (&$targetClinic) use (&$fitsFunc) {
                        /* @var IdealQueueClinic $targetClinic */
                        $res = $targetClinic->availableWeekdayCount > 0 && $fitsFunc($targetClinic->id);

                        return $res;
                    };
                    $advancedFitSuccessful = function (&$targetClinic) use ($queueIndex) {
                        /* @var IdealQueueClinic $targetClinic */
                        $targetClinic->assignWeekday($queueIndex);
                    };
                }
            }

            $targetClinic = $idealQueueClinics[$idealQueue[$queueIndex]];
            if ($advancedFitsFunc($targetClinic)) {
                //the clinic fits yay; that was easy
                $advancedFitSuccessful($targetClinic);
            } else {
                $assignmentFound = false;
                //the search begins; look n to the right, then continue with n+1
                //totalEvents as upper bound; this will not be reached probably
                for ($i = 1; $i < $totalEvents; ++$i) {
                    //n to right
                    $newIndex = $queueIndex + $i;
                    if ($newIndex < $totalEvents) {
                        $targetClinic = $idealQueueClinics[$idealQueue[$newIndex]];
                        if ($advancedFitsFunc($targetClinic)) {
                            //the clinic fits!
                            $advancedFitSuccessful($targetClinic);
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
                        $newTargetClinic = $idealQueueClinics[$idealQueue[$newIndex]];
                        if ($advancedFitsFunc($newTargetClinic)) {
                            //the clinic fits!
                            //now correct the queue
                            //this is in the past! so we need to do corrections
                            //clear history
                            for ($j = $newIndex; $j < $queueIndex; $j++) {
                                $myTargetClinic = $idealQueueClinics[$idealQueue[$newIndex]];
                                $myTargetClinic->removeAssignments($newIndex);
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
                    return $this->returnNodikaError($nodikaOutput, GenerationStatus::NO_ALLOWED_CLINIC_FOR_EVENT);
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
}
