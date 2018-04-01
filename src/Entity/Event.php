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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $startDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $endDateTime;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $confirmDateTime = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastRemainderEmailSent;

    /**
     * @var int
     *
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
     * @var FrontendUser|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\FrontendUser", inversedBy="events")
     */
    private $frontendUser;

    /**
     * @var EventLine
     *
     * @ORM\ManyToOne(targetEntity="EventLine", inversedBy="events")
     */
    private $eventLine;

    /**
     * @var EventLineGeneration|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventLineGeneration", inversedBy="generatedEvents")
     */
    private $generatedBy;

    /**
     * @var EventPast[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventPast", mappedBy="event")
     */
    private $eventPast;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->eventPast = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getStartDateTime(): ?\DateTime
    {
        return $this->startDateTime;
    }

    /**
     * @param \DateTime $startDateTime
     */
    public function setStartDateTime(\DateTime $startDateTime): void
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndDateTime(): ?\DateTime
    {
        return $this->endDateTime;
    }

    /**
     * @param \DateTime $endDateTime
     */
    public function setEndDateTime(\DateTime $endDateTime): void
    {
        $this->endDateTime = $endDateTime;
    }

    /**
     * @return \DateTime|null
     */
    public function getConfirmDateTime(): ?\DateTime
    {
        return $this->confirmDateTime;
    }

    /**
     * @param \DateTime|null $confirmDateTime
     */
    public function setConfirmDateTime(?\DateTime $confirmDateTime): void
    {
        $this->confirmDateTime = $confirmDateTime;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastRemainderEmailSent(): ?\DateTime
    {
        return $this->lastRemainderEmailSent;
    }

    /**
     * @param \DateTime|null $lastRemainderEmailSent
     */
    public function setLastRemainderEmailSent(?\DateTime $lastRemainderEmailSent): void
    {
        $this->lastRemainderEmailSent = $lastRemainderEmailSent;
    }

    /**
     * @return int
     */
    public function getTradeTag(): int
    {
        return $this->tradeTag;
    }

    /**
     * @param int $tradeTag
     */
    public function setTradeTag(int $tradeTag): void
    {
        $this->tradeTag = $tradeTag;
    }

    /**
     * @return Member
     */
    public function getMember(): Member
    {
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember(Member $member): void
    {
        $this->member = $member;
    }

    /**
     * @return FrontendUser|null
     */
    public function getFrontendUser(): ?FrontendUser
    {
        return $this->frontendUser;
    }

    /**
     * @param FrontendUser|null $frontendUser
     */
    public function setFrontendUser(?FrontendUser $frontendUser): void
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * @return EventLine
     */
    public function getEventLine(): EventLine
    {
        return $this->eventLine;
    }

    /**
     * @param EventLine $eventLine
     */
    public function setEventLine(EventLine $eventLine): void
    {
        $this->eventLine = $eventLine;
    }

    /**
     * @return EventLineGeneration|null
     */
    public function getGeneratedBy(): ?EventLineGeneration
    {
        return $this->generatedBy;
    }

    /**
     * @param EventLineGeneration|null $generatedBy
     */
    public function setGeneratedBy(?EventLineGeneration $generatedBy): void
    {
        $this->generatedBy = $generatedBy;
    }

    /**
     * @return EventPast[]|ArrayCollection
     */
    public function getEventPast()
    {
        return $this->eventPast;
    }

    /**
     * returns a short representation of start/end datetime of the event
     *
     * @return string
     */
    public function toShort()
    {
        return
            $this->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT) .
            " - " .
            $this->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }
}
