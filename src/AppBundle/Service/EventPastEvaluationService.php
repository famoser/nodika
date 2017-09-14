<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/09/2017
 * Time: 11:11
 */

namespace AppBundle\Service;


use AppBundle\Entity\Event;
use AppBundle\Entity\EventPast;
use AppBundle\Entity\Person;
use AppBundle\Enum\EventChangeType;
use AppBundle\Model\EventPast\EventPastEvaluation;
use AppBundle\Service\Interfaces\EventPastEvaluationServiceInterface;

class EventPastEvaluationService implements EventPastEvaluationServiceInterface
{
    /**
     * evaluates the EventPast
     *
     * @param EventPast $eventPast
     * @return EventPastEvaluation
     */
    public function createEventPastEvaluation(EventPast $eventPast)
    {
        $eventChangeType = $eventPast->getEventChangeType();
        $eventPastEvaluation = new EventPastEvaluation($eventPast->getChangedAtDateTime(), $eventPast->getChangedByPerson(), $eventChangeType);
        if ($eventChangeType == EventChangeType::MANUALLY_CREATED_BY_ADMIN ||
            $eventChangeType == EventChangeType::GENERATED_BY_ADMIN) {
            $event = json_decode($eventPast->getAfterEventJson());
            return $this->eventNewOccurred($eventPastEvaluation, $event);
        } else {
            $before = json_decode($eventPast->getBeforeEventJson());
            $after = json_decode($eventPast->getAfterEventJson());
            return $this->eventChangeOccurred($eventPastEvaluation, $before, $after);
        }
    }

    /**
     * if only a new event is in the history
     *
     * @param EventPastEvaluation $evaluation
     * @param Event $event
     * @return EventPastEvaluation
     */
    private function eventNewOccurred(EventPastEvaluation $evaluation, Event $event)
    {
        if ($event->getMember() != null) {
            $evaluation->setMemberChanged(null, $event->getMember());
        }
        if ($event->getPerson() != null) {
            $evaluation->setPersonChanged(null, $event->getPerson());
        }
        $evaluation->setStartDateTimeChanged(null, $event->getStartDateTime());
        $evaluation->setEndDateTimeChanged(null, $event->getEndDateTime());
        return $evaluation;
    }

    /**
     * if only a new event is in the history
     *
     * @param EventPastEvaluation $evaluation
     * @param Event $beforeEvent
     * @param Event $afterEvent
     * @return EventPastEvaluation
     */
    private function eventChangeOccurred(EventPastEvaluation $evaluation, Event $beforeEvent, Event $afterEvent)
    {
        if ($beforeEvent->getMember() != $afterEvent->getMember()) {
            $evaluation->setMemberChanged($beforeEvent->getMember(), $afterEvent->getMember());
        }
        if ($beforeEvent->getPerson() != $afterEvent->getPerson()) {
            $evaluation->setPersonChanged($beforeEvent->getPerson(), $afterEvent->getPerson());
        }
        if ($beforeEvent->getStartDateTime() != $afterEvent->getEndDateTime()) {
            $evaluation->setStartDateTimeChanged($beforeEvent->getStartDateTime(), $afterEvent->getEndDateTime());
        }
        if ($beforeEvent->getEndDateTime() != $afterEvent->getEndDateTime()) {
            $evaluation->setEndDateTimeChanged($beforeEvent->getEndDateTime(), $afterEvent->getEndDateTime());
        }
        return $evaluation;
    }

    /**
     * creates the EventPast object
     *
     * @param Person $changePerson
     * @param Event $oldEvent
     * @param Event $newEvent
     * @param $eventChangeType
     * @return EventPast
     */
    public function createEventPast(Person $changePerson, $oldEvent, Event $newEvent, $eventChangeType)
    {
        $eventPast = new EventPast();
        $eventPast->setEvent($newEvent);
        $eventPast->setBeforeEventJson($oldEvent != null ? $oldEvent->createJson() : "");
        $eventPast->setAfterEventJson($newEvent->createJson());
        $eventPast->setChangedAtDateTime(new \DateTime());
        $eventPast->setEventChangeType($eventChangeType);
        $eventPast->setChangedByPerson($changePerson);
        return $eventPast;
    }
}