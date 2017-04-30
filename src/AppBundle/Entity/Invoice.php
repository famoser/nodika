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
use AppBundle\Enum\PaymentStatus;
use AppBundle\Enum\TradeTag;
use Doctrine\ORM\Mapping as ORM;


/**
 * A Member is part of the organisation, and is responsible for the events assigned to it
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvoiceRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Invoice extends BaseEntity
{
    use IdTrait;
    use AddressTrait;
    use ThingTrait;


    /**
     * @ORM\Column(type="datetime")
     */
    private $invoiceDateTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $paymentDateTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $paymentStatus = PaymentStatus::NOT_PAYED;
    
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
