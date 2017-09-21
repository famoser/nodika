<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * A Member is part of the organisation, and is responsible for the events assigned to it
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MemberRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Member extends BaseEntity
{
    use IdTrait;
    use ThingTrait;
    use AddressTrait;
    use CommunicationTrait;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasBeenInvited = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $invitationHash = null;

    /**
     * @var Person[]
     *
     * @ORM\ManyToMany(targetEntity="Person", mappedBy="members")
     */
    private $persons;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="members")
     */
    private $organisation;

    /**
     * @var Event[]
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="member")
     */
    private $events;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->persons = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * Add person
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
     * Remove person
     *
     * @param Person $person
     */
    public function removePerson(Person $person)
    {
        $this->persons->removeElement($person);
    }

    /**
     * Get persons
     *
     * @return \Doctrine\Common\Collections\Collection|Person[]
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * Set organisation
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
     * @return Member
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
     * returns a string representation of this entity
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
        return $this->hasBeenInvited;
    }

    /**
     * @param bool $hasBeenInvited
     */
    public function setHasBeenInvited($hasBeenInvited)
    {
        $this->hasBeenInvited = $hasBeenInvited;
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
