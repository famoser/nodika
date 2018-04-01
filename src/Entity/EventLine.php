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
use App\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventLine groups together events of the same category.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EventLineRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventLine extends BaseEntity
{
    use IdTrait;
    use ThingTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $displayOrder = 1;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="eventLine")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * @var EventLineGeneration[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventLineGeneration", mappedBy="eventLine")
     * @ORM\OrderBy({"createdAtDateTime" = "ASC"})
     */
    private $eventLineGenerations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->eventLineGenerations = new ArrayCollection();
    }

    /**
     * Add event.
     *
     * @param Event $event
     *
     * @return EventLine
     */
    public function addEvent(Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event.
     *
     * @param Event $event
     */
    public function removeEvent(Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events.
     *
     * @return \Doctrine\Common\Collections\Collection|Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add eventLineGeneration.
     *
     * @param EventLineGeneration $eventLineGeneration
     *
     * @return EventLine
     */
    public function addEventLineGeneration(EventLineGeneration $eventLineGeneration)
    {
        $this->eventLineGenerations[] = $eventLineGeneration;

        return $this;
    }

    /**
     * Remove eventLineGeneration.
     *
     * @param EventLineGeneration $eventLineGeneration
     */
    public function removeEventLineGeneration(EventLineGeneration $eventLineGeneration)
    {
        $this->eventLineGenerations->removeElement($eventLineGeneration);
    }

    /**
     * Get eventLineGenerations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventLineGenerations()
    {
        return $this->eventLineGenerations;
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getName();
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param int $displayOrder
     *
     * @return static
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }
}
