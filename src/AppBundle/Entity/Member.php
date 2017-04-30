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
 * A Member is part of the organisation, and is responsible for the events assigned to it
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MemberRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Member extends BaseEntity
{
    use IdTrait;
    use PersonTrait;
    use AddressTrait;
    use CommunicationTrait;

    /**
     * @var Person[]
     *
     * @ORM\ManyToMany(targetEntity="Person", mappedBy="members")
     */
    private $persons;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="members")
     */
    private $organisation;

    /**
     * @var Event[]
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="member")
     */
    private $events;
}
