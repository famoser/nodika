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
use AppBundle\Enum\EventChangeType;
use AppBundle\Enum\TradeTag;
use Doctrine\ORM\Mapping as ORM;


/**
 * A Member is part of the organisation, and is responsible for the events assigned to it
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventPastRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventPast extends BaseEntity
{
    use IdTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    private $changeDateTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $changeType;

    /**
     * @ORM\Column(type="text")
     */
    private $changeConfigurationJson;

    /**
     * @ORM\Column(type="text")
     */
    private $eventJson;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="events")
     */
    private $member;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="events")
     */
    private $person;

    /**
     * @var EventLine
     *
     * @ORM\ManyToOne(targetEntity="EventLine", inversedBy="events")
     */
    private $eventLine;

    /**
     * @var EventPast[]
     *
     * @ORM\OneToMany(targetEntity="EventPast", mappedBy="event")
     */
    private $eventPast;
}
