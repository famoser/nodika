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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Organisation represents one unity of members which distribute Appointments.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Organisation extends BaseEntity
{
    use IdTrait;
    use ThingTrait;
    use AddressTrait;
    use CommunicationTrait;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="datetime")
     */
    private $activeEnd;

    /**
     * @var Person[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Person", inversedBy="leaderOf")
     * @ORM\OrderBy({"familyName" = "ASC", "givenName" = "ASC"})
     */
    private $leaders;

    /**
     * @var Member[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Member", mappedBy="organisation")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $members;

    /**
     * @var Invoice[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="organisation")
     * @ORM\OrderBy({"invoiceDateTime" = "DESC"})
     */
    private $invoices;

    /**
     * @var EventLine[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventLine", mappedBy="organisation")
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    private $eventLines;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->leaders = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->eventLines = new ArrayCollection();
    }

    /**
     * @param Person $person
     *
     * @return Organisation
     */
    public static function createFromPerson(Person $person)
    {
        $organisation = new self();
        $organisation->setAddressFieldsFrom($person);
        $organisation->setCommunicationFieldsFrom($person);

        return $organisation;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isActive.
     *
     * @param bool $isActive
     *
     * @return Organisation
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get activeEnd.
     *
     * @return \DateTime
     */
    public function getActiveEnd()
    {
        return $this->activeEnd;
    }

    /**
     * Set activeEnd.
     *
     * @param \DateTime $activeEnd
     *
     * @return Organisation
     */
    public function setActiveEnd($activeEnd)
    {
        $this->activeEnd = $activeEnd;

        return $this;
    }

    /**
     * Add leader.
     *
     * @param Person $leader
     *
     * @return Organisation
     */
    public function addLeader(Person $leader)
    {
        $this->leaders[] = $leader;

        return $this;
    }

    /**
     * Remove leader.
     *
     * @param Person $leader
     */
    public function removeLeader(Person $leader)
    {
        $this->leaders->removeElement($leader);
    }

    /**
     * Get leaders.
     *
     * @return Collection|Person[]
     */
    public function getLeaders()
    {
        return $this->leaders;
    }

    /**
     * Add member.
     *
     * @param Member $member
     *
     * @return Organisation
     */
    public function addMember(Member $member)
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member.
     *
     * @param Member $member
     */
    public function removeMember(Member $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * Get members.
     *
     * @return Collection|Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add invoice.
     *
     * @param Invoice $invoice
     *
     * @return Organisation
     */
    public function addInvoice(Invoice $invoice)
    {
        $this->invoices[] = $invoice;

        return $this;
    }

    /**
     * Remove invoice.
     *
     * @param Invoice $invoice
     */
    public function removeInvoice(Invoice $invoice)
    {
        $this->invoices->removeElement($invoice);
    }

    /**
     * Get invoices.
     *
     * @return Collection|Invoice[]
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * Add event line.
     *
     * @param EventLine $eventLine
     *
     * @return Organisation
     */
    public function addEventLine(EventLine $eventLine)
    {
        $this->eventLines[] = $eventLine;

        return $this;
    }

    /**
     * Remove event line.
     *
     * @param EventLine $eventLine
     */
    public function removeEventLine(EventLine $eventLine)
    {
        $this->eventLines->removeElement($eventLine);
    }

    /**
     * Get event lines.
     *
     * @return Collection|EventLine[]
     */
    public function getEventLines()
    {
        return $this->eventLines;
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
}
