<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\PersonTrait;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Enum\DistributionType;
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
    private $displayOrder;

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
     */
    private $events;

    /**
     * @var EventLineGeneration[]
     *
     * @ORM\OneToMany(targetEntity="EventLineGeneration", mappedBy="eventLine")
     */
    private $eventLineGenerations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventLineGenerations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return EventLine
     */
    public function setOrganisation(\AppBundle\Entity\Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return \AppBundle\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Add event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return EventLine
     */
    public function addEvent(\AppBundle\Entity\Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event
     *
     * @param \AppBundle\Entity\Event $event
     */
    public function removeEvent(\AppBundle\Entity\Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add eventLineGeneration
     *
     * @param \AppBundle\Entity\EventLineGeneration $eventLineGeneration
     *
     * @return EventLine
     */
    public function addEventLineGeneration(\AppBundle\Entity\EventLineGeneration $eventLineGeneration)
    {
        $this->eventLineGenerations[] = $eventLineGeneration;

        return $this;
    }

    /**
     * Remove eventLineGeneration
     *
     * @param \AppBundle\Entity\EventLineGeneration $eventLineGeneration
     */
    public function removeEventLineGeneration(\AppBundle\Entity\EventLineGeneration $eventLineGeneration)
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
    public function setDisplayOrder(int $displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }
}
