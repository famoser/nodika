<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/2/18
 * Time: 10:10 AM
 */

namespace App\Entity\Traits;


use App\Entity\EventLine;
use App\Entity\EventLineGeneration;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Enum\TradeTag;

trait EventTrait
{
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
    private $lastRemainderEmailSent = null;

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
     * @param EventTrait $eventTrait
     */
    protected function writeValues(EventTrait $eventTrait)
    {
        $this->startDateTime = $eventTrait->getStartDateTime();
        $this->endDateTime = $eventTrait->getEndDateTime();
        $this->confirmDateTime = $eventTrait->getConfirmDateTime();
        $this->lastRemainderEmailSent = $eventTrait->getLastRemainderEmailSent();
        $this->tradeTag = $eventTrait->getTradeTag();
        $this->member = $eventTrait->getMember();
        $this->frontendUser = $eventTrait->getFrontendUser();
        $this->eventLine = $eventTrait->getEventLine();
        $this->generatedBy = $eventTrait->getGeneratedBy();
    }
}