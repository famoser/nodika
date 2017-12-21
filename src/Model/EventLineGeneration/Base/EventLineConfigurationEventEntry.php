<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 17:25
 */

namespace App\Model\EventLineGeneration\Base;


use App\Entity\Event;

class EventLineConfigurationEventEntry
{
    /**
     * MemberConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if ($data != null) {
            $this->memberId = $data->memberId;
            $this->startDateTime = new \DateTime($data->startDateTime->date);
            $this->endDateTime = new \DateTime($data->endDateTime->date);
        }
    }

    /**
     * @param Event $event
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