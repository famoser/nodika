<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/09/2017
 * Time: 11:10
 */

namespace AppBundle\Service\Interfaces;


use AppBundle\Entity\Event;
use AppBundle\Entity\EventPast;
use AppBundle\Entity\Person;
use AppBundle\Model\EventPast\EventPastEvaluation;

interface EventPastEvaluationServiceInterface
{
    /**
     * evaluates the EventPast
     *
     * @param EventPast $eventPast
     * @return EventPastEvaluation
     */
    public function createEventPastEvaluation(EventPast $eventPast);

    /**
     * creates the EventPast object
     *
     * @param Person $changePerson
     * @param Event $oldEvent
     * @param Event $newEvent
     * @param $eventChangeType
     * @return EventPast
     */
    public function createEventPast(Person $changePerson, $oldEvent, Event $newEvent, $eventChangeType);
}