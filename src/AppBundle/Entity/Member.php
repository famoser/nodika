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
        $this->persons = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add person
     *
     * @param \AppBundle\Entity\Person $person
     *
     * @return Member
     */
    public function addPerson(\AppBundle\Entity\Person $person)
    {
        $this->persons[] = $person;

        return $this;
    }

    /**
     * Remove person
     *
     * @param \AppBundle\Entity\Person $person
     */
    public function removePerson(\AppBundle\Entity\Person $person)
    {
        $this->persons->removeElement($person);
    }

    /**
     * Get persons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return Member
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
     * @return Member
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
}
