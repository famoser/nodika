<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/2/18
 * Time: 10:10 AM
 */

namespace App\Entity\Traits;

use App\Entity\EventGeneration;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Enum\EventType;
use App\Enum\TradeTag;
use Doctrine\ORM\Mapping as ORM;

trait EventTrait
{
    use StartEndTrait;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $confirmDateTime = null;

    /**
     * @var FrontendUser|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\FrontendUser")
     */
    private $confirmedBy = null;

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
     * @var int
     * @ORM\Column(type="integer")
     */
    private $eventType = EventType::UNSPECIFIED;

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
     * @var EventGeneration|null
     *
     * @ORM\ManyToOne(targetEntity="EventGeneration", inversedBy="generatedEvents")
     */
    private $generatedBy;

    /**
     * @return int
     */
    public function getEventType(): int
    {
        return $this->eventType;
    }

    /**
     * @param int $eventType
     */
    public function setEventType(int $eventType): void
    {
        $this->eventType = $eventType;
    }

    /**
     * @return \DateTime|null
     */
    public function getConfirmDateTime(): ?\DateTime
    {
        return $this->confirmDateTime;
    }

    /**
     * @return FrontendUser|null
     */
    public function getConfirmedBy(): ?FrontendUser
    {
        return $this->confirmedBy;
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
     * @return Member|null
     */
    public function getMember(): ?Member
    {
        return $this->member;
    }

    /**
     * @param Member|null $member
     */
    public function setMember(?Member $member): void
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
     * @return EventGeneration|null
     */
    public function getGeneratedBy(): ?EventGeneration
    {
        return $this->generatedBy;
    }

    /**
     * @param EventGeneration|null $generatedBy
     */
    public function setGeneratedBy(?EventGeneration $generatedBy): void
    {
        $this->generatedBy = $generatedBy;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->getConfirmDateTime() != null;
    }

    public function confirm(FrontendUser $user)
    {
        $this->confirmDateTime = new \DateTime();
        $this->confirmedBy = $user;
    }

    public function undoConfirm()
    {
        $this->confirmedBy = null;
        $this->confirmDateTime = null;
    }

    /**
     * @param EventTrait $eventTrait
     */
    protected function writeValues($eventTrait)
    {
        $this->setStartDateTime($eventTrait->getStartDateTime());
        $this->setEndDateTime($eventTrait->getEndDateTime());
        $this->confirmDateTime = $eventTrait->getConfirmDateTime();
        $this->confirmedBy = $eventTrait->getConfirmedBy();
        $this->lastRemainderEmailSent = $eventTrait->getLastRemainderEmailSent();
        $this->eventType = $this->getEventType();
        $this->tradeTag = $eventTrait->getTradeTag();
        $this->member = $eventTrait->getMember();
        $this->frontendUser = $eventTrait->getFrontendUser();
        $this->generatedBy = $eventTrait->getGeneratedBy();
    }
}
