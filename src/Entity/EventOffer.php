<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Enum\OfferStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventOffer can be accepted or declined, and allows one Person to propose one or more Events to change
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EventOfferRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventOffer extends BaseEntity
{
    use IdTrait;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createDateTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $openDateTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closeDateTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = OfferStatus::CREATING;

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
        $this->eventOfferEntries = new ArrayCollection();
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
     * @param \DateTime $createDateTime
     *
     * @return EventOffer
     */
    public function setCreateDateTime($createDateTime)
    {
        $this->createDateTime = $createDateTime;

        return $this;
    }

    /**
     * Get openDateTime
     *
     * @return \DateTime
     */
    public function getCreateDateTime()
    {
        return $this->createDateTime;
    }

    /**
     * Set closeDateTime
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
     * Get closeDateTime
     *
     * @return \DateTime
     */
    public function getOpenDateTime()
    {
        return $this->openDateTime;
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
     * Get status
     *
     * @return integer
     */
    public function getStatusText()
    {
        return OfferStatus::getTranslation($this->status);
    }

    /**
     * Set offeredByMember
     *
     * @param Member $offeredByMember
     *
     * @return EventOffer
     */
    public function setOfferedByMember(Member $offeredByMember = null)
    {
        $this->offeredByMember = $offeredByMember;

        return $this;
    }

    /**
     * Get offeredByMember
     *
     * @return Member
     */
    public function getOfferedByMember()
    {
        return $this->offeredByMember;
    }

    /**
     * Set offeredByPerson
     *
     * @param Person $offeredByPerson
     *
     * @return EventOffer
     */
    public function setOfferedByPerson(Person $offeredByPerson = null)
    {
        $this->offeredByPerson = $offeredByPerson;

        return $this;
    }

    /**
     * Get offeredByPerson
     *
     * @return Person
     */
    public function getOfferedByPerson()
    {
        return $this->offeredByPerson;
    }

    /**
     * Set offeredToMember
     *
     * @param Member $offeredToMember
     *
     * @return EventOffer
     */
    public function setOfferedToMember(Member $offeredToMember = null)
    {
        $this->offeredToMember = $offeredToMember;

        return $this;
    }

    /**
     * Get offeredToMember
     *
     * @return Member
     */
    public function getOfferedToMember()
    {
        return $this->offeredToMember;
    }

    /**
     * Set offeredToPerson
     *
     * @param Person $offeredToPerson
     *
     * @return EventOffer
     */
    public function setOfferedToPerson(Person $offeredToPerson = null)
    {
        $this->offeredToPerson = $offeredToPerson;

        return $this;
    }

    /**
     * Get offeredToPerson
     *
     * @return Person
     */
    public function getOfferedToPerson()
    {
        return $this->offeredToPerson;
    }

    /**
     * Add eventOfferEntry
     *
     * @param EventOfferEntry $eventOfferEntry
     *
     * @return EventOffer
     */
    public function addEventOfferEntry(EventOfferEntry $eventOfferEntry)
    {
        $this->eventOfferEntries[] = $eventOfferEntry;

        return $this;
    }

    /**
     * Remove eventOfferEntry
     *
     * @param EventOfferEntry $eventOfferEntry
     */
    public function removeEventOfferEntry(EventOfferEntry $eventOfferEntry)
    {
        $this->eventOfferEntries->removeElement($eventOfferEntry);
    }

    /**
     * Get eventOfferEntries
     *
     * @return \Doctrine\Common\Collections\Collection|EventOfferEntry[]
     */
    public function getEventOfferEntries()
    {
        return $this->eventOfferEntries;
    }

    /**
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getDescription();
    }

    /**
     * @return \DateTime
     */
    public function getCloseDateTime()
    {
        return $this->closeDateTime;
    }

    /**
     * @param \DateTime $closeDateTime
     */
    public function setCloseDateTime($closeDateTime)
    {
        $this->closeDateTime = $closeDateTime;
    }
}
