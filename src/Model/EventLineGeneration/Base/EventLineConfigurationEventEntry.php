<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration\Base;

use App\Entity\Event;

class EventLineConfigurationEventEntry
{
    /**
     * MemberConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (null !== $data) {
            $this->memberId = $data->memberId;
            $this->startDateTime = new \DateTime($data->startDateTime->date);
            $this->endDateTime = new \DateTime($data->endDateTime->date);
        }
    }

    /**
     * @param Event $event
     *
     * @return static
     */
    public static function fromEvent(Event $event)
    {
        $static = new static(null);
        $static->memberId = $event->getMember()->getId();
        $static->startDateTime = $event->getStartDateTime();
        $static->endDateTime = $event->getEndDateTime();

        return $static;
    }

    /* @var int $memberId */
    public $memberId;
    /* @var \DateTime $startDateTime */
    public $startDateTime;
    /* @var \DateTime $endDateTime */
    public $endDateTime;
}
