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
use App\Entity\Traits\ChangeAwareTrait;
use App\Entity\Traits\IdTrait;
use App\Enum\DistributionType;
use App\Helper\DateTimeFormatter;
use App\Model\EventLineGeneration\Base\BaseConfiguration;
use App\Model\EventLineGeneration\Base\BaseOutput;
use App\Model\EventLineGeneration\GenerationResult;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventLineGeneration is the result of one of the generation algorithms.
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventLineGeneration extends BaseEntity
{
    use IdTrait;
    use ChangeAwareTrait;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $applied = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $distributionType = DistributionType::NODIKA;

    /**
     * the input to the algorithm.
     *
     * @ORM\Column(type="text")
     */
    private $distributionConfigurationJson;

    /**
     * the output of the algorithm.
     *
     * @ORM\Column(type="text")
     */
    private $distributionOutputJson;

    /**
     * the result of the generation.
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
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="generatedBy")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $generatedEvents;

    public function __construct()
    {
        $this->generatedEvents = new ArrayCollection();
    }

    /**
     * Get distributionType.
     *
     * @return int
     */
    public function getDistributionType()
    {
        return $this->distributionType;
    }

    /**
     * Set distributionType.
     *
     * @param int $distributionType
     *
     * @return EventLineGeneration
     */
    public function setDistributionType($distributionType)
    {
        $this->distributionType = $distributionType;

        return $this;
    }

    /**
     * Set distributionConfiguration.
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
     * Get distributionConfigurationJson.
     *
     * @return string
     */
    public function getDistributionConfigurationJson()
    {
        return $this->distributionConfigurationJson;
    }

    /**
     * Set distributionConfigurationJson.
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
     * Set distributionOutput.
     *
     * @param BaseOutput $distributionOutput
     *
     * @return EventLineGeneration
     */
    public function setDistributionOutput(BaseOutput $distributionOutput)
    {
        return $this->setDistributionOutputJson(json_encode($distributionOutput));
    }

    /**
     * Get distributionOutputJson.
     *
     * @return string
     */
    public function getDistributionOutputJson()
    {
        return $this->distributionOutputJson;
    }

    /**
     * Set distributionOutputJson.
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
     * Set generationResultJson.
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
     * Get generationResultJson.
     *
     * @return string
     */
    public function getGenerationResultJson()
    {
        return $this->generationResultJson;
    }

    /**
     * Set generationResultJson.
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
     * Get eventLine.
     *
     * @return EventLine
     */
    public function getEventLine()
    {
        return $this->eventLine;
    }

    /**
     * Set eventLine.
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
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getCreatedAtDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }

    /**
     * Get generationDate.
     *
     * @return \DateTime
     */
    public function getCreatedAtDateTime()
    {
        return $this->createdAtDateTime;
    }

    /**
     * Set generationDate.
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

    /**
     * @return Event[]
     */
    public function getGeneratedEvents()
    {
        return $this->generatedEvents;
    }

    /**
     * @return bool
     */
    public function getApplied()
    {
        return $this->applied;
    }

    /**
     * @param bool $applied
     */
    public function setApplied($applied)
    {
        $this->applied = $applied;
    }
}
