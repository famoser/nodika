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

use App\Entity\EventLineGeneration;
use App\Entity\Person;
use App\Model\EventLineGeneration\GenerationResult;
use App\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use App\Model\EventLineGeneration\Nodika\NodikaOutput;
use App\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use App\Model\EventLineGeneration\RoundRobin\RoundRobinOutput;

interface SettingServiceInterface
{
    /**
     * the amount of time which should pass till the remainder emails are sent out
     * if (lastRemainderSent < now - remainderEmailInterval) then sendRemainder()
     *
     * @return \DateInterval
     */
    public function getRemainderEmailInterval();

    /**
     * the range where it is possible to confirm an event
     * if (now > startOfEvent && startOfEvent - canConfirmEventAt < now) then allowConfirmation()
     *
     * @return \DateInterval
     */
    public function getCanConfirmEventAt();

    /**
     * the range where remainder emails are sent out (before the beginning of the event)
     * if (now > startOfEvent && startOfEvent + sendRemainder < now) then doSendRemainder()
     *
     * @return \DateInterval
     */
    public function getSendRemainderBy();

    /**
     * the range where remainder emails are sent out (before the beginning of the event)
     * if (now > startOfEvent && startOfEvent + urgentSendRemainder < now) then doSendRemainder()
     *
     * @return \DateInterval
     */
    public function getUrgentSendRemainderBy();
}
