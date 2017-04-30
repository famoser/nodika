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
use AppBundle\Enum\OfferStatus;
use Doctrine\ORM\Mapping as ORM;


/**
 * A Member is part of the organisation, and is responsible for the events assigned to it
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventOfferRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventOfferEntry extends BaseEntity
{
    use IdTrait;

    /**
     * @var EventOffer
     *
     * @ORM\ManyToOne(targetEntity="EventOffer", inversedBy="eventOfferEntries")
     */
    private $eventOffer;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     */
    private $event;
}
