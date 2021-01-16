<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\CreationAwareTrait;
use App\Entity\Traits\EventTrait;
use App\Entity\Traits\IdTrait;
use App\Enum\EventChangeType;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventPast saves the state of the event when action occurred.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventPast extends BaseEntity
{
    use CreationAwareTrait;
    use EventTrait;
    use IdTrait;

    /**
     * EventPast constructor.
     *
     * @return EventPast
     */
    public static function create(Event $event, int $eventChangeType, Doctor $user)
    {
        $eventPast = new static();
        $eventPast->writeValues($event);
        $eventPast->event = $event;
        $eventPast->eventChangeType = $eventChangeType;
        $eventPast->setCreatedBy($user);

        return $eventPast;
    }

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $eventChangeType = EventChangeType::CREATED;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="eventPast")
     */
    private $event;

    public function getEventChangeType(): int
    {
        return $this->eventChangeType;
    }

    public function getEventChangeTypeText(): string
    {
        return EventChangeType::getText($this->eventChangeType);
    }

    public function setEventChangeType(int $eventChangeType): void
    {
        $this->eventChangeType = $eventChangeType;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }
}
