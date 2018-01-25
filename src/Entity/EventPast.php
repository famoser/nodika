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

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $changedAtDateTime;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $changedByPerson;

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
     * @return \DateTime
     */
    public function getChangedAtDateTime()
    {
        return $this->changedAtDateTime;
    }

    /**
     * @param \DateTime $changedAtDateTime
     */
    public function setChangedAtDateTime($changedAtDateTime)
    {
        $this->changedAtDateTime = $changedAtDateTime;
    }

    /**
     * @return Person
     */
    public function getChangedByPerson()
    {
        return $this->changedByPerson;
    }

    /**
     * @param Person $changedByPerson
     */
    public function setChangedByPerson($changedByPerson)
    {
        $this->changedByPerson = $changedByPerson;
    }

    /**
     * @return int
     */
    public function getEventChangeType()
    {
        return $this->eventChangeType;
    }

    /**
     * @param int $eventChangeType
     */
    public function setEventChangeType($eventChangeType)
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
    public function setBeforeEventJson($beforeEventJson)
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
    public function setAfterEventJson($afterEventJson)
    {
        $this->afterEventJson = $afterEventJson;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getChangedAtDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }
}
