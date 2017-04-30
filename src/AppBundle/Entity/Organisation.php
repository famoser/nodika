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
use AppBundle\Entity\Traits\ThingTrait;
use Doctrine\ORM\Mapping as ORM;


/**
 * An Organisation represents one unity of members which distribute Appointments
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrganisationRepository")
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
     * @var Person[]
     *
     * @ORM\ManyToMany(targetEntity="Person", inversedBy="leaderOf")
     */
    private $leaders;

    /**
     * @var Member[]
     *
     * @ORM\OneToMany(targetEntity="Member", mappedBy="organisation")
     */
    private $members;

    /**
     * @var Invoice[]
     *
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="organisation")
     */
    private $invoices;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->leaders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->invoices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Organisation
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set activeEnd
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
     * Get activeEnd
     *
     * @return \DateTime
     */
    public function getActiveEnd()
    {
        return $this->activeEnd;
    }

    /**
     * Set webpage
     *
     * @param string $webpage
     *
     * @return Organisation
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
     * Add leader
     *
     * @param \AppBundle\Entity\Person $leader
     *
     * @return Organisation
     */
    public function addLeader(\AppBundle\Entity\Person $leader)
    {
        $this->leaders[] = $leader;

        return $this;
    }

    /**
     * Remove leader
     *
     * @param \AppBundle\Entity\Person $leader
     */
    public function removeLeader(\AppBundle\Entity\Person $leader)
    {
        $this->leaders->removeElement($leader);
    }

    /**
     * Get leaders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeaders()
    {
        return $this->leaders;
    }

    /**
     * Add member
     *
     * @param \AppBundle\Entity\Member $member
     *
     * @return Organisation
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
     * Add invoice
     *
     * @param \AppBundle\Entity\Invoice $invoice
     *
     * @return Organisation
     */
    public function addInvoice(\AppBundle\Entity\Invoice $invoice)
    {
        $this->invoices[] = $invoice;

        return $this;
    }

    /**
     * Remove invoice
     *
     * @param \AppBundle\Entity\Invoice $invoice
     */
    public function removeInvoice(\AppBundle\Entity\Invoice $invoice)
    {
        $this->invoices->removeElement($invoice);
    }

    /**
     * Get invoices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInvoices()
    {
        return $this->invoices;
    }
}
