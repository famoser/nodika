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
use App\Entity\Traits\ChangeAwareTrait;
use App\Entity\Traits\EventTrait;
use App\Entity\Traits\IdTrait;
use App\Enum\EventChangeType;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventPast saves the state of the event when action occurred.
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventPast extends BaseEntity
{
    use IdTrait;
    use ChangeAwareTrait;
    use EventTrait;

    /**
     * EventPast constructor.
     * @param Event|null $event
     * @param null $eventChangeType
     * @param FrontendUser|null $user
     */
    public function __construct(Event $event = null, $eventChangeType = null, FrontendUser $user = null)
    {
        if ($event != null && $eventChangeType != null && $user != null) {
            $this->writeValues($event);
            $this->event = $event;
            $this->eventChangeType = $eventChangeType;
            $this->registerChangeBy($user);
        }
    }

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $eventChangeType = EventChangeType::MANUALLY_CREATED_BY_ADMIN;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="eventPast")
     */
    private $event;

    /**
     * @return int
     */
    public function getEventChangeType(): int
    {
        return $this->eventChangeType;
    }

    /**
     * @param int $eventChangeType
     */
    public function setEventChangeType(int $eventChangeType): void
    {
        $this->eventChangeType = $eventChangeType;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }
}
