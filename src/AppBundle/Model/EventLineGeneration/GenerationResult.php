<?php

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:18
 */

namespace AppBundle\Model\EventLineGeneration;


class GenerationResult
{
    public function __construct($data)
    {
        $this->events = [];
        $this->generationDateTime = new \DateTime();
        if ($data != null) {
            $this->generationDateTime = new \DateTime($data->generationDateTime->date);
            if (is_array($data->events)) {
                foreach ($data->events as $event) {
                    $generatedEvent = new GeneratedEvent();
                    $generatedEvent->startDateTime = new \DateTime($event->startDateTime->date);
                    $generatedEvent->endDateTime = new \DateTime($event->endDateTime->date);
                    $generatedEvent->memberId = $event->memberId;
                    $this->events[] = $generatedEvent;
                }
            }
        }
    }

    /* @var \DateTime $generationDateTime */
    public $generationDateTime;

    /* @var GeneratedEvent[] $events */
    public $events;
}