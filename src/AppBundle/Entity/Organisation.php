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
     * @var Person[]
     *
     * @ORM\ManyToMany(targetEntity="Person", inversedBy="organisations")
     */
    private $leaders;

    /**
     * @var Invoice[]
     *
     * @ORM\OneToMany(targetEntity="Invoice", inversedBy="organisation")
     */
    private $invoices;

    /**
     * @var ActivationPeriod[]
     *
     * @ORM\OneToMany(targetEntity="ActivationPeriod", inversedBy="organisation")
     */
    private $activationPeriods;
}
