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
 * @ORM\HasLifecycleCallbacks
 */
class EventOffer extends BaseEntity
{
    use IdTrait;
    use ChangeAwareTrait;

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
    private $senderAuthorizationStatus = AuthorizationStatus::PENDING;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->eventsWhichChangeOwner = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
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

    /**
     * @return Doctor
     */
    public function getReceiver(): Doctor
    {
        return $this->receiver;
    }

    /**
     * @param Doctor $receiver
     */
    public function setReceiver(Doctor $receiver): void
    {
        $this->receiver = $receiver;
    }

    /**
     * @return Doctor
     */
    public function getSender(): Doctor
    {
        return $this->sender;
    }

    /**
     * @param Doctor $sender
     */
    public function setSender(Doctor $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return Clinic
     */
    public function getReceiverClinic(): Clinic
    {
        return $this->receiverClinic;
    }

    /**
     * @param Clinic $receiverClinic
     */
    public function setReceiverClinic(Clinic $receiverClinic): void
    {
        $this->receiverClinic = $receiverClinic;
    }

    /**
     * @return Clinic
     */
    public function getSenderClinic(): Clinic
    {
        return $this->senderClinic;
    }

    /**
     * @param Clinic $senderClinic
     */
    public function setSenderClinic(Clinic $senderClinic): void
    {
        $this->senderClinic = $senderClinic;
    }

    /**
     * @param Doctor $doctor
     *
     * @return bool
     */
    public function accept(Doctor $doctor)
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::PENDING, AuthorizationStatus::DECLINED, AuthorizationStatus::WITHDRAWN], AuthorizationStatus::ACCEPTED);
    }

    /**
     * @param Doctor $doctor
     *
     * @return bool
     */
    public function decline(Doctor $doctor)
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::PENDING], AuthorizationStatus::DECLINED);
    }

    /**
     * @param Doctor $doctor
     *
     * @return bool
     */
    public function withdraw(Doctor $doctor)
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::ACCEPTED], AuthorizationStatus::WITHDRAWN);
    }

    /**
     * @param Doctor $doctor
     *
     * @return bool
     */
    public function acknowledge(Doctor $doctor)
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::PENDING, AuthorizationStatus::ACCEPTED], AuthorizationStatus::ACKNOWLEDGED);
    }

    public function showReceiver()
    {
        if ($this->getIsResolved()) {
            return false;
        }

        $senderStatus = $this->senderAuthorizationStatus;
        $receiverStatus = $this->receiverAuthorizationStatus;

        return
            //either not responded yet
            (AuthorizationStatus::ACCEPTED === $senderStatus && AuthorizationStatus::PENDING === $receiverStatus) ||
            //or sender has withdrawn
            (AuthorizationStatus::WITHDRAWN === $senderStatus && AuthorizationStatus::ACKNOWLEDGED !== $receiverStatus);
    }

    public function showSender()
    {
        if ($this->getIsResolved()) {
            return false;
        }

        $senderStatus = $this->senderAuthorizationStatus;
        $receiverStatus = $this->receiverAuthorizationStatus;

        return
            //either no answer so far
            AuthorizationStatus::PENDING === $receiverStatus ||
            //or has been answered but now acknowledged yet
            (\in_array($receiverStatus, [AuthorizationStatus::ACCEPTED, AuthorizationStatus::DECLINED], true) && AuthorizationStatus::ACKNOWLEDGED !== $senderStatus);
    }

    public function tryMarkAsResolved()
    {
        if (!$this->showReceiver() && !$this->showSender()) {
            $this->isResolved = true;
        }

        return $this->isResolved;
    }

    /**
     * @param Doctor $doctor
     * @param int[]  $sourceStates
     * @param int    $targetState
     *
     * @return bool
     */
    private function changeStatus(Doctor $doctor, $sourceStates, $targetState)
    {
        if ($this->isResolved) {
            return false;
        }

        $sourceStates = array_combine($sourceStates, [$targetState]);
        if ($doctor === $this->getReceiver() && $doctor->getClinics()->contains($this->getReceiverClinic()) && \in_array($this->receiverAuthorizationStatus, $sourceStates, true)) {
            $this->receiverAuthorizationStatus = $targetState;

            return true;
        } elseif ($doctor === $this->getSender() && $doctor->getClinics()->contains($this->getSenderClinic()) && \in_array($this->senderAuthorizationStatus, $sourceStates, true)) {
            $this->senderAuthorizationStatus = $targetState;

            return true;
        }

        //close if

        return false;
    }

    private $cacheSenderOwned = null;
    private $cacheReceiverOwned = null;
    private $cacheIsValid = null;

    /**
     * orders the events & checks if trade is valid.
     */
    private function refreshCache()
    {
        if (null === $this->cacheIsValid) {
            $this->cacheReceiverOwned = [];
            $this->cacheSenderOwned = [];
            $this->cacheIsValid = true;

            //check events have correct owner
            foreach ($this->getEventsWhichChangeOwner() as $item) {
                if ($this->getReceiverClinic() === $item->getClinic() &&
                    (null === $item->getDoctor() || $item->getDoctor() === $this->getReceiver())) {
                    $this->cacheReceiverOwned[] = $item;
                } elseif ($this->getSenderClinic() === $item->getClinic() &&
                    (null === $item->getDoctor() || $item->getDoctor() === $this->getSender())) {
                    $this->cacheSenderOwned[] = $item;
                } else {
                    $this->cacheIsValid = false;
                }

                if ($item->getClinic()->isDeleted()) {
                    $this->cacheIsValid = false;
                }
            }

            //check doctors not removed
            if ($this->getReceiver()->isDeleted() || $this->getSender()->isDeleted()) {
                $this->cacheIsValid = false;
            }

            //check clinics not removed
            if ($this->getReceiverClinic()->isDeleted() || $this->getSenderClinic()->isDeleted()) {
                $this->cacheIsValid = false;
            }

            //check doctors still in clinic
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

        return $this->cacheSenderOwned;
    }
}
