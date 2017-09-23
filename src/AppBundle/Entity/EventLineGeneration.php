<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Enum\DistributionType;
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Model\EventLineGeneration\Base\BaseConfiguration;
use AppBundle\Model\EventLineGeneration\Base\BaseOutput;
use AppBundle\Model\EventLineGeneration\GenerationResult;
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
    private $createdAtDateTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $distributionType = DistributionType::NODIKA;

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
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Person")
     */
    private $createdByPerson;

    /**
     * Set generationDate
     *
     * @param \DateTime $createdAtDateTime
     *
     * @return EventLineGeneration
     */
    public function setCreatedAtDateTime($createdAtDateTime)
    {
        $this->createdAtDateTime = $createdAtDateTime;

        return $this;
    }

    /**
     * Get generationDate
     *
     * @return \DateTime
     */
    public function getCreatedAtDateTime()
    {
        return $this->createdAtDateTime;
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
     * Set distributionConfiguration
     *
     * @param BaseConfiguration $distributionConfiguration
     *
     * @return EventLineGeneration
     */
    public function setDistributionConfiguration(BaseConfiguration $distributionConfiguration)
    {
        return $this->setDistributionConfigurationJson(json_encode($distributionConfiguration));
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
     * Set distributionOutput
     *
     * @param BaseOutput $distributionOutput
     * @return EventLineGeneration
     */
    public function setDistributionOutput(BaseOutput $distributionOutput)
    {
        return $this->setDistributionOutputJson(json_encode($distributionOutput));
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
     * Set generationResultJson
     *
     * @param GenerationResult $generationResult
     *
     * @return EventLineGeneration
     */
    public function setGenerationResult(GenerationResult $generationResult)
    {
        return $this->setGenerationResultJson(json_encode($generationResult));
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
     * @param EventLine $eventLine
     *
     * @return EventLineGeneration
     */
    public function setEventLine(EventLine $eventLine = null)
    {
        $this->eventLine = $eventLine;

        return $this;
    }

    /**
     * Get eventLine
     *
     * @return EventLine
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
        return $this->getCreatedAtDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }

    /**
     * @return Person
     */
    public function getCreatedByPerson()
    {
        return $this->createdByPerson;
    }

    /**
     * @param Person $createdByPerson
     */
    public function setCreatedByPerson($createdByPerson)
    {
        $this->createdByPerson = $createdByPerson;
    }
}
