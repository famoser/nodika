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
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EventOfferEntryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventOfferEntry extends BaseEntity
{
    use IdTrait;

    /**
     * @var EventOffer
     *
     * @ORM\ManyToOne(targetEntity="EventOffer", inversedBy="eventOfferEntries")
     */
    private $eventOffer;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     */
    private $event;

    /**
     * Get eventOffer.
     *
     * @return EventOffer
     */
    public function getEventOffer()
    {
        return $this->eventOffer;
    }

    /**
     * Set eventOffer.
     *
     * @param EventOffer $eventOffer
     *
     * @return EventOfferEntry
     */
    public function setEventOffer(EventOffer $eventOffer = null)
    {
        $this->eventOffer = $eventOffer;

        return $this;
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set event.
     *
     * @param Event $event
     *
     * @return EventOfferEntry
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return 'entry';
    }
}
