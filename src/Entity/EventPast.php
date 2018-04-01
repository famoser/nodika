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
use App\Entity\Traits\IdTrait;
use App\Enum\EventChangeType;
use App\Helper\DateTimeFormatter;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventPast saves the state of the event when action occurred.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EventPastRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventPast extends BaseEntity
{
    use IdTrait;
    use ChangeAwareTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $eventChangeType = EventChangeType::MANUALLY_CREATED_BY_ADMIN;

    /**
     * the event before the change occurred.
     *
     * @ORM\Column(type="text")
     */
    private $beforeEventJson;

    /**
     * the event after the change occurred.
     *
     * @ORM\Column(type="text")
     */
    private $afterEventJson;

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
     * @return mixed
     */
    public function getBeforeEventJson()
    {
        return $this->beforeEventJson;
    }

    /**
     * @param mixed $beforeEventJson
     */
    public function setBeforeEventJson($beforeEventJson): void
    {
        $this->beforeEventJson = $beforeEventJson;
    }

    /**
     * @return mixed
     */
    public function getAfterEventJson()
    {
        return $this->afterEventJson;
    }

    /**
     * @param mixed $afterEventJson
     */
    public function setAfterEventJson($afterEventJson): void
    {
        $this->afterEventJson = $afterEventJson;
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
