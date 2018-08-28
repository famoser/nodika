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
use App\Enum\OfferStatus;
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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status = OfferStatus::OPEN;

    /**
     * @var EventOfferEntry[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventOfferEntry", mappedBy="eventOffer", cascade={"all"})
     */
    private $entries;

    /**
     * @var Doctor
     *
     * @ORM\ManyToOne(targetEntity="Doctor")
     */
    private $receiverSignature;

    /**
     * @var Doctor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Doctor")
     */
    private $senderSignature;

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
    private $senderAuthorizationStatus = AuthorizationStatus::SIGNED;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return EventOffer
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set description.
     *
     * @param string $message
     *
     * @return EventOffer
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get eventOfferEntries.
     *
     * @return ArrayCollection|EventOfferEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    public function isValid()
    {//TODO refactor
        //check all events are still valid authorized
        $authorizations = [];
        foreach ($this->getEntries() as $entry) {
            if (null === $entry->getEventOfferAuthorization()) {
                return false;
            }

            //check target still valid
            $targetDoc = $entry->getTargetDoctor();
            $targetClinic = $entry->getTargetClinic();
            if (null === $targetDoc || null === $targetClinic ||
                $targetDoc->isDeleted() || $targetClinic->isDeleted() ||
                !$targetDoc->getClinics()->contains($targetClinic)) {
                return false;
            }

            //save authorization to check sender afterwards
            $authorizations[$entry->getEventOfferAuthorization()->getId()] = $entry->getEventOfferAuthorization();
        }

        foreach ($authorizations as $authorization) {
            $doctor = $authorization->getReceiverSignature();
            if ($doctor->isDeleted()) {
                return false;
            }

            //find clinic

            //check both are alive & connected
        }

        //check events belong to user
        foreach ($myEvents as $myEvent) {
            if ($myEvent->getClinic() !== $sourceClinic) {
                return false;
            }

            if (null !== $myEvent->getDoctor() && $myEvent->getDoctor() !== $sourceUser) {
                return false;
            }
        }
    }
}
