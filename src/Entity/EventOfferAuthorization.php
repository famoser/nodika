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
use App\Enum\SignatureStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventOfferEntry is part of an EventOffer, and specified which events are about to be traded.
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventOfferAuthorization extends BaseEntity
{
    use IdTrait;

    /**
     * @var EventOffer
     *
     * @ORM\ManyToOne(targetEntity="EventOffer", inversedBy="authorizations")
     */
    private $eventOffer;

    /**
     * @var FrontendUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\FrontendUser")
     */
    private $signedBy;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $signatureStatus = SignatureStatus::PENDING;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $decisionDateTime;

    /**
     * @var EventOfferEntry[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventOfferEntry", mappedBy="eventOfferAuthorization")
     */
    private $authorizes;

    /**
     * EventOfferAuthorization constructor.
     */
    public function __construct()
    {
        $this->authorizes = new ArrayCollection();
    }

    /**
     * @return EventOffer
     */
    public function getEventOffer(): EventOffer
    {
        return $this->eventOffer;
    }

    /**
     * @param EventOffer $eventOffer
     */
    public function setEventOffer(EventOffer $eventOffer): void
    {
        $this->eventOffer = $eventOffer;
    }

    /**
     * @return FrontendUser
     */
    public function getSignedBy(): FrontendUser
    {
        return $this->signedBy;
    }

    /**
     * @param FrontendUser $signedBy
     */
    public function setSignedBy(FrontendUser $signedBy): void
    {
        $this->signedBy = $signedBy;
    }

    /**
     * @return int
     */
    public function getSignatureStatus(): int
    {
        return $this->signatureStatus;
    }

    /**
     * @param int $signatureStatus
     */
    public function setSignatureStatus(int $signatureStatus): void
    {
        $this->signatureStatus = $signatureStatus;
    }

    /**
     * @return \DateTime|null
     */
    public function getDecisionDateTime(): ?\DateTime
    {
        return $this->decisionDateTime;
    }

    /**
     * @param \DateTime|null $decisionDateTime
     */
    public function setDecisionDateTime(?\DateTime $decisionDateTime): void
    {
        $this->decisionDateTime = $decisionDateTime;
    }

    /**
     * @return EventOfferEntry[]|ArrayCollection
     */
    public function getAuthorizes()
    {
        return $this->authorizes;
    }
}
