<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;


/**
 * An EventOfferEntry is part of an EventOffer, and specified which events are about to be traded
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventOfferEntryRepository")
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
     * Set eventOffer
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
     * Get eventOffer
     *
     * @return EventOffer
     */
    public function getEventOffer()
    {
        return $this->eventOffer;
    }

    /**
     * Set event
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
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return "entry";
    }
}
