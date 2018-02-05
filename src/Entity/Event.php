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
use App\Enum\TradeTag;
use App\Helper\DateTimeFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Event is a time unit which is assigned to a member or a person.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Event extends BaseEntity
{
    use IdTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDateTime;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isConfirmed = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $isConfirmedDateTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDateTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $tradeTag = TradeTag::MAYBE_TRADE;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="events")
     */
    private $member;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="events")
     */
    private $person;

    /**
     * @var EventLine
     *
     * @ORM\ManyToOne(targetEntity="EventLine", inversedBy="events")
     */
    private $eventLine;

    /**
     * @var EventPast[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventPast", mappedBy="event")
     */
    private $eventPast;

    /**
     * @var EventLineGeneration
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventLineGeneration", inversedBy="generatedEvents")
     */
    private $generatedBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastRemainderEmailSent;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->eventPast = new ArrayCollection();
    }

    /**
     * Add eventPast.
     *
     * @param EventPast $eventPast
     *
     * @return Event
     */
    public function addEventPast(EventPast $eventPast)
    {
        $this->eventPast[] = $eventPast;

        return $this;
    }

    /**
     * Remove eventPast.
     *
     * @param EventPast $eventPast
     */
    public function removeEventPast(EventPast $eventPast)
    {
        $this->eventPast->removeElement($eventPast);
    }

    /**
     * Get eventPast.
     *
     * @return \Doctrine\Common\Collections\Collection|EventPast[]
     */
    public function getEventPast()
    {
        return $this->eventPast;
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT) . ' - ' . $this->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }

    /**
     * Get startDateTime.
     *
     * @return \DateTime
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * Set startDateTime.
     *
     * @param \DateTime $startDateTime
     *
     * @return Event
     */
    public function setStartDateTime($startDateTime)
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    /**
     * Get endDateTime.
     *
     * @return \DateTime
     */
    public function getEndDateTime()
    {
        return $this->endDateTime;
    }

    /**
     * Set endDateTime.
     *
     * @param \DateTime $endDateTime
     *
     * @return Event
     */
    public function setEndDateTime($endDateTime)
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }

    /**
     * creates a json representation of the object.
     *
     * @return string
     */
    public function createJson()
    {
        $pseudoObject = new \stdClass();
        $pseudoObject->id = $this->getId();
        $pseudoObject->startDateTime = $this->getStartDateTime();
        $pseudoObject->endDateTime = $this->getEndDateTime();
        $pseudoObject->eventLineId = null !== $this->getEventLine() ? $this->getEventLine()->getId() : null;
        $pseudoObject->memberId = null !== $this->getMember() ? $this->getMember()->getId() : null;
        $pseudoObject->personId = null !== $this->getPerson() ? $this->getPerson()->getId() : null;
        $pseudoObject->tradeTag = $this->getTradeTag();

        return json_encode($pseudoObject);
    }

    /**
     * Get eventLine.
     *
     * @return EventLine
     */
    public function getEventLine()
    {
        return $this->eventLine;
    }

    /**
     * Set eventLine.
     *
     * @param EventLine $eventLine
     *
     * @return Event
     */
    public function setEventLine(EventLine $eventLine = null)
    {
        $this->eventLine = $eventLine;

        return $this;
    }

    /**
     * Get member.
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set member.
     *
     * @param Member $member
     *
     * @return Event
     */
    public function setMember(Member $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get person.
     *
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set person.
     *
     * @param Person $person
     *
     * @return Event
     */
    public function setPerson(Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get tradeTag.
     *
     * @return int
     */
    public function getTradeTag()
    {
        return $this->tradeTag;
    }

    /**
     * Set tradeTag.
     *
     * @param int $tradeTag
     *
     * @return Event
     */
    public function setTradeTag($tradeTag)
    {
        $this->tradeTag = $tradeTag;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsConfirmed()
    {
        return $this->isConfirmed;
    }

    /**
     * @param mixed $isConfirmed
     */
    public function setIsConfirmed($isConfirmed)
    {
        $this->isConfirmed = $isConfirmed;
    }

    /**
     * @return \DateTime
     */
    public function getIsConfirmedDateTime()
    {
        return $this->isConfirmedDateTime;
    }

    /**
     * @param \DateTime $isConfirmedDateTime
     */
    public function setIsConfirmedDateTime($isConfirmedDateTime)
    {
        $this->isConfirmedDateTime = $isConfirmedDateTime;
    }

    /**
     * @return EventLineGeneration
     */
    public function getGeneratedBy()
    {
        return $this->generatedBy;
    }

    /**
     * @param EventLineGeneration $generatedBy
     */
    public function setGeneratedBy(EventLineGeneration $generatedBy)
    {
        $this->generatedBy = $generatedBy;
    }

    /**
     * @return \DateTime
     */
    public function getLastRemainderEmailSent()
    {
        return $this->lastRemainderEmailSent;
    }

    /**
     * @param \DateTime $lastRemainderEmailSent
     */
    public function setLastRemainderEmailSent($lastRemainderEmailSent)
    {
        $this->lastRemainderEmailSent = $lastRemainderEmailSent;
    }
}
