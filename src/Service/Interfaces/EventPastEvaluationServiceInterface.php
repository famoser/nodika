<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\Event;
use App\Entity\EventPast;
use App\Entity\FrontendUser;
use App\Entity\Person;
use App\Model\EventPast\EventPastEvaluation;

interface EventPastEvaluationServiceInterface
{
    /**
     * evaluates the EventPast.
     *
     * @param EventPast $eventPast
     *
     * @return EventPastEvaluation
     */
    public function createEventPastEvaluation(EventPast $eventPast);

    /**
     * creates the EventPast object.
     *
     * @param FrontendUser $changePerson
     * @param Event $oldEvent
     * @param Event $newEvent
     * @param $eventChangeType
     *
     * @return EventPast
     */
    public function createEventPast(FrontendUser $changePerson, $oldEvent, Event $newEvent, $eventChangeType);
}
