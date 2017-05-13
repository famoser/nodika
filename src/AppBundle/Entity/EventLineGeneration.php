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
use AppBundle\Helper\DateTimeFormatter;
use Doctrine\ORM\Mapping as ORM;


/**
 * An EventLineGeneration is the result of one of the generation algorithms
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

    /**
     * Set generationDate
     *
     * @param \DateTime $generationDate
     *
     * @return EventLineGeneration
     */
    public function setGenerationDate($generationDate)
    {
        $this->generationDate = $generationDate;

        return $this;
    }

    /**
     * Get generationDate
     *
     * @return \DateTime
     */
    public function getGenerationDate()
    {
        return $this->generationDate;
    }

    /**
     * Set distributionType
     *
     * @param integer $distributionType
     *
     * @return EventLineGeneration
     */
    public function setDistributionType($distributionType)
    {
        $this->distributionType = $distributionType;

        return $this;
    }

    /**
     * Get distributionType
     *
     * @return integer
     */
    public function getDistributionType()
    {
        return $this->distributionType;
    }

    /**
     * Set distributionConfigurationJson
     *
     * @param string $distributionConfigurationJson
     *
     * @return EventLineGeneration
     */
    public function setDistributionConfigurationJson($distributionConfigurationJson)
    {
        $this->distributionConfigurationJson = $distributionConfigurationJson;

        return $this;
    }

    /**
     * Get distributionConfigurationJson
     *
     * @return string
     */
    public function getDistributionConfigurationJson()
    {
        return $this->distributionConfigurationJson;
    }

    /**
     * Set distributionOutputJson
     *
     * @param string $distributionOutputJson
     *
     * @return EventLineGeneration
     */
    public function setDistributionOutputJson($distributionOutputJson)
    {
        $this->distributionOutputJson = $distributionOutputJson;

        return $this;
    }

    /**
     * Get distributionOutputJson
     *
     * @return string
     */
    public function getDistributionOutputJson()
    {
        return $this->distributionOutputJson;
    }

    /**
     * Set generationResultJson
     *
     * @param string $generationResultJson
     *
     * @return EventLineGeneration
     */
    public function setGenerationResultJson($generationResultJson)
    {
        $this->generationResultJson = $generationResultJson;

        return $this;
    }

    /**
     * Get generationResultJson
     *
     * @return string
     */
    public function getGenerationResultJson()
    {
        return $this->generationResultJson;
    }

    /**
     * Set eventLine
     *
     * @param \AppBundle\Entity\EventLine $eventLine
     *
     * @return EventLineGeneration
     */
    public function setEventLine(\AppBundle\Entity\EventLine $eventLine = null)
    {
        $this->eventLine = $eventLine;

        return $this;
    }

    /**
     * Get eventLine
     *
     * @return \AppBundle\Entity\EventLine
     */
    public function getEventLine()
    {
        return $this->eventLine;
    }

    /**
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getGenerationDate()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }
}
