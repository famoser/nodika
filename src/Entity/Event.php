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
use App\Entity\Traits\EventTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Helper\DateTimeFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Event is a time unit which is assigned to a clinic or a person.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Event extends BaseEntity
{
    use IdTrait;
    use EventTrait;
    use SoftDeleteTrait;

    /**
     * @var EventPast[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventPast", mappedBy="event", cascade={"all"})
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private $eventPast;

    /**
     * @var EventTag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventTag")
     * @ORM\JoinTable(name="event_event_tags")
     */
    private $eventTags;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->eventPast = new ArrayCollection();
        $this->eventTags = new ArrayCollection();
    }

    /**
     * @return EventPast[]|ArrayCollection
     */
    public function getEventPast()
    {
        return $this->eventPast;
    }

    /**
     * returns a short representation of start/end datetime of the event
     *
     * @return string
     */
    public function toShort()
    {
        return
            $this->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT) .
            " - " .
            $this->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }

    /**
     * @return EventTag[]|ArrayCollection
     */
    public function getEventTags()
    {
        return $this->eventTags;
    }
}
