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
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventOfferEntry is part of an EventOffer, and specified which events are about to be traded.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventOfferEntry extends BaseEntity
{
    use IdTrait;

    /**
     * @var EventOffer
     *
     * @ORM\ManyToOne(targetEntity="EventOffer", inversedBy="entries")
     */
    private $eventOffer;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     */
    private $event;

    /**
     * @var Doctor
     *
     * @ORM\ManyToOne(targetEntity="Doctor")
     */
    private $targetDoctor;

    /**
     * @var Clinic
     *
     * @ORM\ManyToOne(targetEntity="Clinic")
     */
    private $targetClinic;

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
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return Doctor
     */
    public function getTargetDoctor(): Doctor
    {
        return $this->targetDoctor;
    }

    /**
     * @param Doctor $targetDoctor
     */
    public function setTargetDoctor(Doctor $targetDoctor): void
    {
        $this->targetDoctor = $targetDoctor;
    }

    /**
     * @return Clinic
     */
    public function getTargetClinic(): Clinic
    {
        return $this->targetClinic;
    }

    /**
     * @param Clinic $targetClinic
     */
    public function setTargetClinic(Clinic $targetClinic): void
    {
        $this->targetClinic = $targetClinic;
    }
}
