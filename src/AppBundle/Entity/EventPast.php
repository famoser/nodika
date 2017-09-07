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
use AppBundle\Enum\EventChangeType;
use AppBundle\Helper\DateTimeFormatter;
use Doctrine\ORM\Mapping as ORM;


/**
 * An EventPast saves the state of the event when action occurred
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventPastRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventPast extends BaseEntity
{
    use IdTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    private $changeDateTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $changeType = EventChangeType::CREATED_BY_ADMIN;

    /**
     * information about the change which occurred
     *
     * @ORM\Column(type="text")
     */
    private $changeConfigurationJson;

    /**
     * the event after the change occurred
     *
     * @ORM\Column(type="text")
     */
    private $eventJson;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="eventPast")
     */
    private $event;

    /**
     * Set changeDateTime
     *
     * @param \DateTime $changeDateTime
     *
     * @return EventPast
     */
    public function setChangeDateTime($changeDateTime)
    {
        $this->changeDateTime = $changeDateTime;

        return $this;
    }

    /**
     * Get changeDateTime
     *
     * @return \DateTime
     */
    public function getChangeDateTime()
    {
        return $this->changeDateTime;
    }

    /**
     * Set changeType
     *
     * @param integer $changeType
     *
     * @return EventPast
     */
    public function setChangeType($changeType)
    {
        $this->changeType = $changeType;

        return $this;
    }

    /**
     * Get changeType
     *
     * @return integer
     */
    public function getChangeType()
    {
        return $this->changeType;
    }

    /**
     * Set changeConfigurationJson
     *
     * @param string $changeConfigurationJson
     *
     * @return EventPast
     */
    public function setChangeConfigurationJson($changeConfigurationJson)
    {
        $this->changeConfigurationJson = $changeConfigurationJson;

        return $this;
    }

    /**
     * Get changeConfigurationJson
     *
     * @return string
     */
    public function getChangeConfigurationJson()
    {
        return $this->changeConfigurationJson;
    }

    /**
     * Set eventJson
     *
     * @param string $eventJson
     *
     * @return EventPast
     */
    public function setEventJson($eventJson)
    {
        $this->eventJson = $eventJson;

        return $this;
    }

    /**
     * Get eventJson
     *
     * @return string
     */
    public function getEventJson()
    {
        return $this->eventJson;
    }

    /**
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return EventPast
     */
    public function setEvent(\AppBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \AppBundle\Entity\Event
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
        return $this->getChangeDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }
}
