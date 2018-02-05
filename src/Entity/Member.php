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
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Member is part of the organisation, and is responsible for the events assigned to it.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Member extends BaseEntity
{
    use IdTrait;
    use ThingTrait;
    use AddressTrait;
    use CommunicationTrait;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $invitationDateTime = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $invitationHash = null;

    /**
     * @var Person[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Person", mappedBy="members")
     * @ORM\OrderBy({"familyName" = "ASC", "givenName" = "ASC"})
     */
    private $persons;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="members")
     */
    private $organisation;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="member")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->persons = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * Add person.
     *
     * @param Person $person
     *
     * @return Member
     */
    public function addPerson(Person $person)
    {
        $this->persons[] = $person;

        return $this;
    }

    /**
     * Remove person.
     *
     * @param Person $person
     */
    public function removePerson(Person $person)
    {
        $this->persons->removeElement($person);
    }

    /**
     * Get persons.
     *
     * @return \Doctrine\Common\Collections\Collection|Person[]
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * Get organisation.
     *
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set organisation.
     *
     * @param Organisation $organisation
     *
     * @return Member
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Add event.
     *
     * @param Event $event
     *
     * @return Member
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
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getName();
    }

    /**
     * @return bool
     */
    public function getHasBeenInvited()
    {
        return null !== $this->getInvitationDateTime();
    }

    /**
     * @return \DateTime
     */
    public function getInvitationDateTime()
    {
        return $this->invitationDateTime;
    }

    /**
     * @param mixed $invitationDateTime
     */
    public function setInvitationDateTime($invitationDateTime)
    {
        $this->invitationDateTime = $invitationDateTime;
    }

    /**
     * @return string
     */
    public function getInvitationHash()
    {
        return $this->invitationHash;
    }

    /**
     * @param string $invitationHash
     */
    public function setInvitationHash($invitationHash)
    {
        $this->invitationHash = $invitationHash;
    }
}
