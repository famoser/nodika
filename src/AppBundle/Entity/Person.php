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
     * @ORM\ManyToMany(targetEntity="Organisation", inversedBy="leaders")
     */
    private $leaderOf;

    /**
     * @var Member[]
     *
     * @ORM\ManyToMany(targetEntity="Member", inversedBy="persons")
     * @ORM\JoinTable(name="product_attributes_product")
     */
    private $members;

    /**
     * @var User[]
     *
     * @ORM\OneToMany(targetEntity="User", mappedBy="person")
     */
    private $users;

    /**
     * @var Event[]
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="person")
     */
    private $events;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->leaderOf = new \Doctrine\Common\Collections\ArrayCollection();
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set webpage
     *
     * @param string $webpage
     *
     * @return Person
     */
    public function setWebpage($webpage)
    {
        $this->webpage = $webpage;

        return $this;
    }

    /**
     * Get webpage
     *
     * @return string
     */
    public function getWebpage()
    {
        return $this->webpage;
    }

    /**
     * Add leaderOf
     *
     * @param \AppBundle\Entity\Organisation $leaderOf
     *
     * @return Person
     */
    public function addLeaderOf(\AppBundle\Entity\Organisation $leaderOf)
    {
        $this->leaderOf[] = $leaderOf;

        return $this;
    }

    /**
     * Remove leaderOf
     *
     * @param \AppBundle\Entity\Organisation $leaderOf
     */
    public function removeLeaderOf(\AppBundle\Entity\Organisation $leaderOf)
    {
        $this->leaderOf->removeElement($leaderOf);
    }

    /**
     * Get leaderOf
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeaderOf()
    {
        return $this->leaderOf;
    }

    /**
     * Add member
     *
     * @param \AppBundle\Entity\Member $member
     *
     * @return Person
     */
    public function addMember(\AppBundle\Entity\Member $member)
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member
     *
     * @param \AppBundle\Entity\Member $member
     */
    public function removeMember(\AppBundle\Entity\Member $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Person
     */
    public function addUser(\AppBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\User $user
     */
    public function removeUser(\AppBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Person
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
