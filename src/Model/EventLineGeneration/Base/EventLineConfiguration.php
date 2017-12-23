<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 17:25
 */

namespace App\Model\EventLineGeneration\Base;

use App\Entity\Event;
use App\Entity\EventLine;

class EventLineConfiguration
{
    /**
     * MemberConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if ($data != null) {
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

    /**
     * @param EventLine $eventLine
     * @return static
     */
    public static function createFromEventLine(EventLine $eventLine)
    {
        $val = new static(null);
        $val->id = $eventLine->getId();
        $val->name = $eventLine->getName();
        return $val;
    }

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
}
