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
use App\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventLine groups together events of the same category.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EventLineRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventLine extends BaseEntity
{
    use IdTrait;
    use ThingTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $displayOrder = 1;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="eventLine", cascade={"all"})
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * @var EventGeneration[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventGeneration", mappedBy="eventLine", cascade={"all"})
     * @ORM\OrderBy({"createdAtDateTime" = "ASC"})
     */
    private $eventLineGenerations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->eventLineGenerations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param int $displayOrder
     *
     * @return static
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
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
     * Get eventLineGenerations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventLineGenerations()
    {
        return $this->eventLineGenerations;
    }
}
