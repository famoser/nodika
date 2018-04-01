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
use App\Entity\Traits\ChangeAwareTrait;
use App\Enum\OfferStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventOffer can be accepted or declined, and allows one Person to propose one or more Events to change.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EventOfferRepository")
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
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $openDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closeDateTime;

    /**
     * @var int
     *
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
     * @var EventOfferEntry[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventOfferEntry", mappedBy="eventOffer")
     */
    private $eventOfferEntries;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->eventOfferEntries = new ArrayCollection();
    }

    /**
     * Get closeDateTime.
     *
     * @return \DateTime
     */
    public function getOpenDateTime()
    {
        return $this->openDateTime;
    }

    /**
     * Set closeDateTime.
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
     * Get status.
     *
     * @return int
     */
    public function getStatusText()
    {
        return OfferStatus::getTranslation($this->status);
    }

    /**
     * Get offeredByMember.
     *
     * @return Member
     */
    public function getOfferedByMember()
    {
        return $this->offeredByMember;
    }

    /**
     * Set offeredByMember.
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
     * Get offeredByPerson.
     *
     * @return Person
     */
    public function getOfferedByPerson()
    {
        return $this->offeredByPerson;
    }

    /**
     * Set offeredByPerson.
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
     * Get offeredToMember.
     *
     * @return Member
     */
    public function getOfferedToMember()
    {
        return $this->offeredToMember;
    }

    /**
     * Set offeredToMember.
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
     * Get offeredToPerson.
     *
     * @return Person
     */
    public function getOfferedToPerson()
    {
        return $this->offeredToPerson;
    }

    /**
     * Set offeredToPerson.
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
     * Add eventOfferEntry.
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
     * Remove eventOfferEntry.
     *
     * @param EventOfferEntry $eventOfferEntry
     */
    public function removeEventOfferEntry(EventOfferEntry $eventOfferEntry)
    {
        $this->eventOfferEntries->removeElement($eventOfferEntry);
    }

    /**
     * Get eventOfferEntries.
     *
     * @return \Doctrine\Common\Collections\Collection|EventOfferEntry[]
     */
    public function getEventOfferEntries()
    {
        return $this->eventOfferEntries;
    }
    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
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
