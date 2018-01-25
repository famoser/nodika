<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration;

class GenerationResult
{
    public function __construct($data)
    {
        $this->events = [];
        $this->generationDateTime = new \DateTime();
        if (null !== $data) {
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
