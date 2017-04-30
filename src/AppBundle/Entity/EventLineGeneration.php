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
use AppBundle\Enum\DistributionType;
use Doctrine\ORM\Mapping as ORM;


/**
 * A Member is part of the organisation, and is responsible for the events assigned to it
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventLineGenerationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventLineGeneration extends BaseEntity
{
    use IdTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    private $generationDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $distributionType = DistributionType::FAIR;

    /**
     * @ORM\Column(type="text")
     */
    private $distributionConfigurationJson;

    /**
     * @ORM\Column(type="text")
     */
    private $distributionOutputJson;

    /**
     * @ORM\Column(type="text")
     */
    private $generationResultJson;

    /**
     * @var EventLine
     *
     * @ORM\ManyToOne(targetEntity="EventLine", inversedBy="eventLineGenerations")
     */
    private $eventLine;
}
