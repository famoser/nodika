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
use AppBundle\Enum\OfferStatus;
use Doctrine\ORM\Mapping as ORM;


/**
 * An EventOffer can be accepted or declined, and allows one Person to propose one or more Events to change
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventOfferRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventOffer extends BaseEntity
{
    use IdTrait;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $openDateTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $closeDateTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = OfferStatus::OFFER_OPEN;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     */
    private $offeredByMember;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $offeredByPerson;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     */
    private $offeredToMember;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $offeredToPerson;

    /**
     * @var EventOfferEntry[]
     *
     * @ORM\OneToMany(targetEntity="EventOfferEntry", mappedBy="eventOffer")
     */
    private $eventOfferEntries;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->eventOfferEntries = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return EventOffer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set openDateTime
     *
     * @param \DateTime $openDateTime
     *
     * @return EventOffer
     */
    public function setOpenDateTime($openDateTime)
    {
        $this->openDateTime = $openDateTime;

        return $this;
    }

    /**
     * Get openDateTime
     *
     * @return \DateTime
     */
    public function getOpenDateTime()
    {
        return $this->openDateTime;
    }

    /**
     * Set closeDateTime
     *
     * @param \DateTime $closeDateTime
     *
     * @return EventOffer
     */
    public function setCloseDateTime($closeDateTime)
    {
        $this->closeDateTime = $closeDateTime;

        return $this;
    }

    /**
     * Get closeDateTime
     *
     * @return \DateTime
     */
    public function getCloseDateTime()
    {
        return $this->closeDateTime;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return EventOffer
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set offeredByMember
     *
     * @param \AppBundle\Entity\Member $offeredByMember
     *
     * @return EventOffer
     */
    public function setOfferedByMember(\AppBundle\Entity\Member $offeredByMember = null)
    {
        $this->offeredByMember = $offeredByMember;

        return $this;
    }

    /**
     * Get offeredByMember
     *
     * @return \AppBundle\Entity\Member
     */
    public function getOfferedByMember()
    {
        return $this->offeredByMember;
    }

    /**
     * Set offeredByPerson
     *
     * @param \AppBundle\Entity\Person $offeredByPerson
     *
     * @return EventOffer
     */
    public function setOfferedByPerson(\AppBundle\Entity\Person $offeredByPerson = null)
    {
        $this->offeredByPerson = $offeredByPerson;

        return $this;
    }

    /**
     * Get offeredByPerson
     *
     * @return \AppBundle\Entity\Person
     */
    public function getOfferedByPerson()
    {
        return $this->offeredByPerson;
    }

    /**
     * Set offeredToMember
     *
     * @param \AppBundle\Entity\Member $offeredToMember
     *
     * @return EventOffer
     */
    public function setOfferedToMember(\AppBundle\Entity\Member $offeredToMember = null)
    {
        $this->offeredToMember = $offeredToMember;

        return $this;
    }

    /**
     * Get offeredToMember
     *
     * @return \AppBundle\Entity\Member
     */
    public function getOfferedToMember()
    {
        return $this->offeredToMember;
    }

    /**
     * Set offeredToPerson
     *
     * @param \AppBundle\Entity\Person $offeredToPerson
     *
     * @return EventOffer
     */
    public function setOfferedToPerson(\AppBundle\Entity\Person $offeredToPerson = null)
    {
        $this->offeredToPerson = $offeredToPerson;

        return $this;
    }

    /**
     * Get offeredToPerson
     *
     * @return \AppBundle\Entity\Person
     */
    public function getOfferedToPerson()
    {
        return $this->offeredToPerson;
    }

    /**
     * Add eventOfferEntry
     *
     * @param \AppBundle\Entity\EventOfferEntry $eventOfferEntry
     *
     * @return EventOffer
     */
    public function addEventOfferEntry(\AppBundle\Entity\EventOfferEntry $eventOfferEntry)
    {
        $this->eventOfferEntries[] = $eventOfferEntry;

        return $this;
    }

    /**
     * Remove eventOfferEntry
     *
     * @param \AppBundle\Entity\EventOfferEntry $eventOfferEntry
     */
    public function removeEventOfferEntry(\AppBundle\Entity\EventOfferEntry $eventOfferEntry)
    {
        $this->eventOfferEntries->removeElement($eventOfferEntry);
    }

    /**
     * Get eventOfferEntries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventOfferEntries()
    {
        return $this->eventOfferEntries;
    }
}