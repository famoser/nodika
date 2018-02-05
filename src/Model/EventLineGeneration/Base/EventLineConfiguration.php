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
use App\Entity\EventLine;

class EventLineConfiguration
{
    /* @var int $id */
    public $id;
    /* @var string $name */
    public $name;
    /* @var bool $isEnabled */
    public $isEnabled = false;
    /* @var bool $eventsSet */
    public $eventsSet = false;
    /* @var EventLineConfigurationEventEntry[] $eventEntries */
    public $eventEntries = [];

    /**
     * MemberConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (null !== $data) {
            $this->id = $data->id;
            $this->name = $data->name;
            $this->isEnabled = $data->isEnabled;
            foreach ($data->eventEntries as $eventEntry) {
                $this->eventEntries[] = new EventLineConfigurationEventEntry($eventEntry);
            }
        } else {
            $this->isEnabled = false;
            $this->eventsSet = false;
            $this->eventEntries = [];
        }
    }

    /**
     * @param EventLine $eventLine
     *
     * @return static
     */
    public static function createFromEventLine(EventLine $eventLine)
    {
        $val = new static(null);
        $val->id = $eventLine->getId();
        $val->name = $eventLine->getName();

        return $val;
    }

    /**
     * @param Event[] $events
     */
    public function setEvents($events)
    {
        $this->eventEntries = [];
        foreach ($events as $event) {
            $this->eventEntries[] = EventLineConfigurationEventEntry::fromEvent($event);
        }
        $this->eventsSet = true;
    }
}
