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
use AppBundle\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * An EventLine groups together events of the same category
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventLineRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventLine extends BaseEntity
{
    use IdTrait;
    use ThingTrait;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $displayOrder = 1;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="eventLines")
     */
    private $organisation;

    /**
     * @var Event[]
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="eventLine")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * @var EventLineGeneration[]
     *
     * @ORM\OneToMany(targetEntity="EventLineGeneration", mappedBy="eventLine")
     * @ORM\OrderBy({"createdAtDateTime" = "ASC"})
     */
    private $eventLineGenerations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->eventLineGenerations = new ArrayCollection();
    }

    /**
     * Set organisation
     *
     * @param Organisation $organisation
     *
     * @return EventLine
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Add event
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
     * Remove event
     *
     * @param Event $event
     */
    public function removeEvent(Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection|Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add eventLineGeneration
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
     * Remove eventLineGeneration
     *
     * @param EventLineGeneration $eventLineGeneration
     */
    public function removeEventLineGeneration(EventLineGeneration $eventLineGeneration)
    {
        $this->eventLineGenerations->removeElement($eventLineGeneration);
    }

    /**
     * Get eventLineGenerations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventLineGenerations()
    {
        return $this->eventLineGenerations;
    }

    /**
     * returns a string representation of this entity
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
     * @return static
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }
}
