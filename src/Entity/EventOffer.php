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
use App\Entity\Traits\ChangeAwareTrait;
use App\Entity\Traits\IdTrait;
use App\Enum\OfferStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventOffer can be accepted or declined, and allows one Person to propose one or more Events to change.
 *
 * @ORM\Entity()
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
    private $message;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status = OfferStatus::CREATING;

    /**
     * @var EventOfferEntry[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventOfferEntry", mappedBy="eventOffer")
     */
    private $entries;

    /**
     * @var EventOfferAuthorization[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventOfferAuthorization", mappedBy="eventOffer")
     */
    private $authorizations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->authorizations = new ArrayCollection();
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
     * Get description.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set description.
     *
     * @param string $message
     *
     * @return EventOffer
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get eventOfferEntries.
     *
     * @return ArrayCollection|EventOfferEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param EventOfferEntry[]|ArrayCollection $entries
     */
    public function setEntries($entries): void
    {
        $this->entries = $entries;
    }

    /**
     * @return EventOfferAuthorization[]|ArrayCollection
     */
    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /**
     * @param EventOfferAuthorization[]|ArrayCollection $authorizations
     */
    public function setAuthorizations($authorizations): void
    {
        $this->authorizations = $authorizations;
    }
}
