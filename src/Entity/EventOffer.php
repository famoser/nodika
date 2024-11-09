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
use App\Enum\AuthorizationStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventOffer can be accepted or declined, and allows one Person to propose one or more Events to change.
 *
 * @ORM\Entity()
 *
 * @ORM\HasLifecycleCallbacks
 */
class EventOffer extends BaseEntity
{
    use ChangeAwareTrait;
    use IdTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isResolved = false;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Event")
     *
     * @ORM\JoinTable(name="event_offer_events")
     */
    private $eventsWhichChangeOwner;

    /**
     * @var Doctor
     *
     * @ORM\ManyToOne(targetEntity="Doctor")
     */
    private $receiver;

    /**
     * @var Clinic
     *
     * @ORM\ManyToOne(targetEntity="Clinic")
     */
    private $receiverClinic;

    /**
     * @var Doctor
     *
     * @ORM\ManyToOne(targetEntity="Doctor")
     */
    private $sender;

    /**
     * @var Clinic
     *
     * @ORM\ManyToOne(targetEntity="Clinic")
     */
    private $senderClinic;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $receiverAuthorizationStatus = AuthorizationStatus::PENDING;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $senderAuthorizationStatus = AuthorizationStatus::ACCEPTED;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->eventsWhichChangeOwner = new ArrayCollection();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getIsResolved(): bool
    {
        return $this->isResolved;
    }

    /**
     * @return Event[]|ArrayCollection
     */
    public function getEventsWhichChangeOwner()
    {
        return $this->eventsWhichChangeOwner;
    }

    public function getReceiver(): Doctor
    {
        return $this->receiver;
    }

    public function setReceiver(Doctor $receiver): void
    {
        $this->receiver = $receiver;
    }

    public function getSender(): Doctor
    {
        return $this->sender;
    }

    public function setSender(Doctor $sender): void
    {
        $this->sender = $sender;
    }

    public function getReceiverClinic(): Clinic
    {
        return $this->receiverClinic;
    }

    public function setReceiverClinic(Clinic $receiverClinic): void
    {
        $this->receiverClinic = $receiverClinic;
    }

    public function getSenderClinic(): Clinic
    {
        return $this->senderClinic;
    }

    public function setSenderClinic(Clinic $senderClinic): void
    {
        $this->senderClinic = $senderClinic;
    }

    /**
     * @return bool
     */
    public function accept(Doctor $doctor)
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::PENDING, AuthorizationStatus::DECLINED, AuthorizationStatus::WITHDRAWN], AuthorizationStatus::ACCEPTED);
    }

    /**
     * @return bool
     */
    public function decline(Doctor $doctor)
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::PENDING], AuthorizationStatus::DECLINED);
    }

    /**
     * @return bool
     */
    public function withdraw(Doctor $doctor)
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::ACCEPTED], AuthorizationStatus::WITHDRAWN);
    }

    /**
     * @return bool
     */
    public function acknowledge(Doctor $doctor)
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::PENDING, AuthorizationStatus::ACCEPTED, AuthorizationStatus::DECLINED, AuthorizationStatus::WITHDRAWN], AuthorizationStatus::ACKNOWLEDGED);
    }

    public const ACCEPT_DECLINE = 1;
    public const ACK_ACCEPTED = 2;
    public const ACK_DECLINED = 3;
    public const WITHDRAW = 4;
    public const ACK_WITHDRAWN = 5;
    public const ACK_INVALID = 6;
    public const NONE = 7;

    public function getPendingAction(Doctor $doctor)
    {
        if ($this->getIsResolved() || $this->getEventsWhichChangeOwner()->isEmpty()) {
            return self::NONE;
        }

        $senderStatus = $this->senderAuthorizationStatus;
        $receiverStatus = $this->receiverAuthorizationStatus;

        if ($doctor === $this->receiver) {
            if (!$this->isValid()) {
                if (AuthorizationStatus::ACKNOWLEDGED !== $this->receiverAuthorizationStatus) {
                    return self::ACK_INVALID;
                }

                return self::NONE;
            }

            // no response yet
            if (AuthorizationStatus::PENDING === $receiverStatus) {
                if (AuthorizationStatus::ACCEPTED === $senderStatus) {
                    return self::ACCEPT_DECLINE;
                } elseif (AuthorizationStatus::WITHDRAWN === $senderStatus) {
                    return self::ACK_WITHDRAWN;
                }
            }
        } elseif ($doctor === $this->sender) {
            if (!$this->isValid()) {
                if (AuthorizationStatus::ACKNOWLEDGED !== $this->senderAuthorizationStatus) {
                    return self::ACK_INVALID;
                }

                return self::NONE;
            }

            // withdraw/ack
            if (AuthorizationStatus::ACCEPTED === $senderStatus) {
                if (AuthorizationStatus::PENDING === $receiverStatus) {
                    return self::WITHDRAW;
                } elseif (AuthorizationStatus::ACCEPTED === $receiverStatus) {
                    return self::ACK_ACCEPTED;
                } elseif (AuthorizationStatus::DECLINED === $receiverStatus) {
                    return self::ACK_DECLINED;
                }
            }
        }

        return self::NONE;
    }

    public function tryMarkAsResolved()
    {
        if (self::NONE === $this->getPendingAction($this->receiver)
            && self::NONE === $this->getPendingAction($this->sender)) {
            $this->isResolved = true;
        }

        return $this->isResolved;
    }

    /**
     * @param int[] $sourceStates
     * @param int   $targetState
     *
     * @return bool
     */
    private function changeStatus(Doctor $doctor, $sourceStates, $targetState)
    {
        if ($this->isResolved) {
            return false;
        }

        $sourceStates = array_merge($sourceStates, [$targetState]);
        if ($doctor === $this->getReceiver() && $doctor->getClinics()->contains($this->getReceiverClinic()) && \in_array($this->receiverAuthorizationStatus, $sourceStates, true)) {
            $this->receiverAuthorizationStatus = $targetState;

            return true;
        } elseif ($doctor === $this->getSender() && $doctor->getClinics()->contains($this->getSenderClinic()) && \in_array($this->senderAuthorizationStatus, $sourceStates, true)) {
            $this->senderAuthorizationStatus = $targetState;

            return true;
        }

        // close if

        return false;
    }

    private $cacheSenderOwned;
    private $cacheReceiverOwned;
    private $cacheIsValid;

    /**
     * orders the events & checks if trade is valid.
     */
    private function refreshCache()
    {
        if (null === $this->cacheIsValid) {
            $this->cacheReceiverOwned = [];
            $this->cacheSenderOwned = [];
            $this->cacheIsValid = true;

            // check events have correct owner
            foreach ($this->getEventsWhichChangeOwner() as $item) {
                if ($this->getReceiverClinic() === $item->getClinic()
                    && (null === $item->getDoctor() || $item->getDoctor() === $this->getReceiver())) {
                    $this->cacheReceiverOwned[] = $item;
                } elseif ($this->getSenderClinic() === $item->getClinic()
                    && (null === $item->getDoctor() || $item->getDoctor() === $this->getSender())) {
                    $this->cacheSenderOwned[] = $item;
                } else {
                    $this->cacheIsValid = false;
                }

                if ($item->getClinic()->isDeleted()) {
                    $this->cacheIsValid = false;
                }
            }

            // check doctors not removed
            if ($this->getReceiver()->isDeleted() || $this->getSender()->isDeleted()) {
                $this->cacheIsValid = false;
            }

            // check clinics not removed
            if ($this->getReceiverClinic()->isDeleted() || $this->getSenderClinic()->isDeleted()) {
                $this->cacheIsValid = false;
            }

            // check doctors still in clinic
            if (!$this->getReceiver()->getClinics()->contains($this->getReceiverClinic()) || !$this->getSender()->getClinics()->contains($this->getSenderClinic())) {
                $this->cacheIsValid = false;
            }
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $this->refreshCache();

        return $this->cacheIsValid;
    }

    /**
     * @return bool
     */
    public function canExecute()
    {
        return AuthorizationStatus::ACCEPTED === $this->receiverAuthorizationStatus && AuthorizationStatus::ACCEPTED === $this->senderAuthorizationStatus;
    }

    /**
     * @return Event[]
     */
    public function getSenderOwnedEvents()
    {
        $this->refreshCache();

        return $this->cacheSenderOwned;
    }

    /**
     * @return Event[]
     */
    public function getReceiverOwnedEvents()
    {
        $this->refreshCache();

        return $this->cacheReceiverOwned;
    }
}
