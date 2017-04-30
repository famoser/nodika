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
 * An EventLineGeneration is the result of the generation algorithm
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
     * the input to the algorithm
     *
     * @ORM\Column(type="text")
     */
    private $distributionConfigurationJson;

    /**
     * the output of the algorithm
     *
     * @ORM\Column(type="text")
     */
    private $distributionOutputJson;

    /**
     * the result of the generation
     *
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
