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
use AppBundle\Entity\Traits\PersonTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * An Person represents a real live Person
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PersonRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Person extends BaseEntity
{
    use IdTrait;
    use PersonTrait;
    use AddressTrait;
    use CommunicationTrait;

    /**
     * @var Organisation[]
     *
     * @ORM\ManyToMany(targetEntity="Organisation", mappedBy="leaders")
     * @ORM\JoinTable(name="person_organisations")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $leaderOf;

    /**
     * @var Member[]
     *
     * @ORM\ManyToMany(targetEntity="Member", inversedBy="persons")
     * @ORM\JoinTable(name="person_members")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $members;

    /**
     * @var FrontendUser
     *
     * @ORM\OneToOne(targetEntity="FrontendUser", mappedBy="person")
     */
    private $frontendUser;

    /**
     * @var Event[]
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="person")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->leaderOf = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * Add leaderOf
     *
     * @param Organisation $leaderOf
     *
     * @return Person
     */
    public function addLeaderOf(Organisation $leaderOf)
    {
        $this->leaderOf[] = $leaderOf;

        return $this;
    }

    /**
     * Remove leaderOf
     *
     * @param Organisation $leaderOf
     */
    public function removeLeaderOf(Organisation $leaderOf)
    {
        $this->leaderOf->removeElement($leaderOf);
    }

    /**
     * Get leaderOf
     *
     * @return \Doctrine\Common\Collections\Collection|Organisation[]
     */
    public function getLeaderOf()
    {
        return $this->leaderOf;
    }

    /**
     * Add member
     *
     * @param Member $member
     *
     * @return Person
     */
    public function addMember(Member $member)
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member
     *
     * @param Member $member
     */
    public function removeMember(Member $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection|Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add event
     *
     * @param Event $event
     *
     * @return Person
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
     * @return \Doctrine\Common\Collections\Collection
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
        return $this->getFullName();
    }

    /**
     * @return FrontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }

    /**
     * @param FrontendUser $frontendUser
     */
    public function setFrontendUser($frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }
}
