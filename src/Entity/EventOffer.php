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
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class EventOffer extends BaseEntity
{
    use ChangeAwareTrait;
    use IdTrait;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private string $message;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $isResolved = false;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\Event>
     */
    #[ORM\JoinTable(name: 'event_offer_events')]
    #[ORM\ManyToMany(targetEntity: Event::class)]
    private \Doctrine\Common\Collections\Collection $eventsWhichChangeOwner;

    #[ORM\ManyToOne(targetEntity: \Doctor::class)]
    private Doctor $receiver;

    #[ORM\ManyToOne(targetEntity: \Clinic::class)]
    private Clinic $receiverClinic;

    #[ORM\ManyToOne(targetEntity: \Doctor::class)]
    private Doctor $sender;

    #[ORM\ManyToOne(targetEntity: \Clinic::class)]
    private Clinic $senderClinic;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $receiverAuthorizationStatus = AuthorizationStatus::PENDING;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $senderAuthorizationStatus = AuthorizationStatus::ACCEPTED;

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

    public function getIsResolved(): bool
    {
        return $this->isResolved;
    }

    /**
     * @return Event[]|ArrayCollection
     */
    public function getEventsWhichChangeOwner(): \Doctrine\Common\Collections\Collection
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

    public function accept(Doctor $doctor): bool
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::PENDING, AuthorizationStatus::DECLINED, AuthorizationStatus::WITHDRAWN], AuthorizationStatus::ACCEPTED);
    }

    public function decline(Doctor $doctor): bool
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::PENDING], AuthorizationStatus::DECLINED);
    }

    public function withdraw(Doctor $doctor): bool
    {
        return $this->changeStatus($doctor, [AuthorizationStatus::ACCEPTED], AuthorizationStatus::WITHDRAWN);
    }

    public function acknowledge(Doctor $doctor): bool
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

    public function getPendingAction(Doctor $doctor): int
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
                }
                if (AuthorizationStatus::WITHDRAWN === $senderStatus) {
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
                }
                if (AuthorizationStatus::ACCEPTED === $receiverStatus) {
                    return self::ACK_ACCEPTED;
                }
                if (AuthorizationStatus::DECLINED === $receiverStatus) {
                    return self::ACK_DECLINED;
                }
            }
        }

        return self::NONE;
    }

    public function tryMarkAsResolved(): ?bool
    {
        if (self::NONE === $this->getPendingAction($this->receiver)
            && self::NONE === $this->getPendingAction($this->sender)) {
            $this->isResolved = true;
        }

        return $this->isResolved;
    }

    /**
     * @param int[] $sourceStates
     */
    private function changeStatus(Doctor $doctor, array $sourceStates, int $targetState): bool
    {
        if ($this->isResolved) {
            return false;
        }

        $sourceStates = array_merge($sourceStates, [$targetState]);
        if ($doctor === $this->getReceiver() && $doctor->getClinics()->contains($this->getReceiverClinic()) && \in_array($this->receiverAuthorizationStatus, $sourceStates, true)) {
            $this->receiverAuthorizationStatus = $targetState;

            return true;
        }
        if ($doctor === $this->getSender() && $doctor->getClinics()->contains($this->getSenderClinic()) && \in_array($this->senderAuthorizationStatus, $sourceStates, true)) {
            $this->senderAuthorizationStatus = $targetState;

            return true;
        }

        // close if

        return false;
    }

    private $cacheSenderOwned;
    private $cacheReceiverOwned;
    private ?bool $cacheIsValid = null;

    /**
     * orders the events & checks if trade is valid.
     */
    private function refreshCache(): void
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

    public function isValid(): ?bool
    {
        $this->refreshCache();

        return $this->cacheIsValid;
    }

    public function canExecute(): bool
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
