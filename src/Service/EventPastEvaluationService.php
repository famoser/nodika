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
use App\Entity\EventPast;
use App\Entity\Person;
use App\Enum\EventChangeType;
use App\Model\Event\DeserializedEvent;
use App\Model\EventPast\EventPastEvaluation;
use App\Service\Interfaces\EventPastEvaluationServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class EventPastEvaluationService implements EventPastEvaluationServiceInterface
{
    /* @var RegistryInterface $doctrine */
    private $doctrine;

    public function __construct(RegistryInterface $registry)
    {
        $this->doctrine = $registry;
    }

    /**
     * evaluates the EventPast.
     *
     * @param EventPast $eventPast
     *
     * @return EventPastEvaluation
     */
    public function createEventPastEvaluation(EventPast $eventPast)
    {
        $eventChangeType = $eventPast->getEventChangeType();
        $eventPastEvaluation = new EventPastEvaluation($eventPast->getChangedAtDateTime(), $eventPast->getChangedByPerson(), $eventChangeType);
        if (EventChangeType::MANUALLY_CREATED_BY_ADMIN === $eventChangeType ||
            EventChangeType::GENERATED_BY_ADMIN === $eventChangeType) {
            $event = json_decode($eventPast->getAfterEventJson());

            return $this->eventNewOccurred($eventPastEvaluation, $event);
        }
        $before = json_decode($eventPast->getBeforeEventJson());
        $after = json_decode($eventPast->getAfterEventJson());

        return $this->eventChangeOccurred($eventPastEvaluation, $before, $after);
    }

    /**
     * if only a new event is in the history.
     *
     * @param EventPastEvaluation $evaluation
     * @param DeserializedEvent   $event
     *
     * @return EventPastEvaluation
     */
    private function eventNewOccurred(EventPastEvaluation $evaluation, $event)
    {
        if (null !== $event->memberId) {
            $this->setMemberChanged($evaluation, null, $event->memberId);
        }
        if (null !== $event->personId) {
            $this->setPersonChanged($evaluation, null, $event->personId);
        }
        $evaluation->setStartDateTimeChanged(null, new \DateTime($event->startDateTime->date));
        $evaluation->setEndDateTimeChanged(null, new \DateTime($event->endDateTime->date));

        return $evaluation;
    }

    /**
     * if only a new event is in the history.
     *
     * @param EventPastEvaluation $evaluation
     * @param DeserializedEvent   $beforeEvent
     * @param DeserializedEvent   $afterEvent
     *
     * @return EventPastEvaluation
     */
    private function eventChangeOccurred(EventPastEvaluation $evaluation, $beforeEvent, $afterEvent)
    {
        if ($beforeEvent->memberId !== $afterEvent->memberId) {
            $this->setMemberChanged($evaluation, $beforeEvent->memberId, $afterEvent->memberId);
        }
        if ($beforeEvent->personId !== $afterEvent->personId) {
            $this->setPersonChanged($evaluation, $beforeEvent->personId, $afterEvent->personId);
        }
        if ($beforeEvent->startDateTime->date !== $afterEvent->startDateTime->date) {
            $evaluation->setStartDateTimeChanged(new \DateTime($beforeEvent->startDateTime->date), new \DateTime($afterEvent->startDateTime->date));
        }
        if ($beforeEvent->endDateTime->date !== $afterEvent->endDateTime->date) {
            $evaluation->setStartDateTimeChanged(new \DateTime($beforeEvent->endDateTime->date), new \DateTime($afterEvent->endDateTime->date));
        }

        return $evaluation;
    }

    /**
     * @param EventPastEvaluation $evaluation
     * @param $oldMemberId
     * @param $newMemberId
     */
    private function setMemberChanged(EventPastEvaluation $evaluation, $oldMemberId, $newMemberId)
    {
        $memberRepo = $this->doctrine->getRepository('App:Member');
        $oldMember = is_numeric($oldMemberId) ? $memberRepo->find($oldMemberId) : null;
        $newMember = is_numeric($newMemberId) ? $memberRepo->find($newMemberId) : null;
        $evaluation->setMemberChanged($oldMember, $newMember);
    }

    /**
     * @param EventPastEvaluation $evaluation
     * @param $oldPersonId
     * @param $newPersonId
     */
    private function setPersonChanged(EventPastEvaluation $evaluation, $oldPersonId, $newPersonId)
    {
        $personRepo = $this->doctrine->getRepository('App:Person');
        $oldPerson = is_numeric($oldPersonId) ? $personRepo->find($oldPersonId) : null;
        $newPerson = is_numeric($newPersonId) ? $personRepo->find($newPersonId) : null;
        $evaluation->setPersonChanged($oldPerson, $newPerson);
    }

    /**
     * creates the EventPast object.
     *
     * @param Person $changePerson
     * @param Event  $oldEvent
     * @param Event  $newEvent
     * @param $eventChangeType
     *
     * @return EventPast
     */
    public function createEventPast(Person $changePerson, $oldEvent, Event $newEvent, $eventChangeType)
    {
        $eventPast = new EventPast();
        $eventPast->setEvent($newEvent);
        $eventPast->setBeforeEventJson(null !== $oldEvent ? $oldEvent->createJson() : '');
        $eventPast->setAfterEventJson($newEvent->createJson());
        $eventPast->setChangedAtDateTime(new \DateTime());
        $eventPast->setEventChangeType($eventChangeType);
        $eventPast->setChangedByPerson($changePerson);

        return $eventPast;
    }
}
