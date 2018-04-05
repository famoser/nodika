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
use App\Entity\Traits\StartEndTrait;
use App\Entity\Traits\ThingTrait;
use App\Enum\DistributionType;
use App\Helper\DateTimeFormatter;
use App\Model\EventLineGeneration\Base\BaseConfiguration;
use App\Model\EventLineGeneration\Base\BaseOutput;
use App\Model\EventLineGeneration\GenerationResult;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventGeneration is the result of one of the generation algorithms.
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventGeneration extends BaseEntity
{
    use IdTrait;
    use ThingTrait;
    use StartEndTrait;
    use ChangeAwareTrait;

    /**
     * in average event lengths
     * so add all event lengths, divide through event count, multiply with $minimalGapBetweenEvents to get the minimal gap between a participant
     *
     * @var double
     *
     * @ORM\Column(type="decimal")
     */
    private $minimalGapBetweenEvents = 1;

    /**
     * a cron expression specifies when a new event starts
     * https://crontab.guru/
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $cronExpression = "* 8 * * *";

    /**
     * @var double
     *
     * @ORM\Column(type="decimal")
     */
    private $weekdayWeight = 1;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal")
     */
    private $saturdayWeight = 1;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal")
     */
    private $sundayWeight = 1;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal")
     */
    private $holidayWeight = 1;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @var EventLine
     *
     * @ORM\ManyToOne(targetEntity="EventLine", inversedBy="eventLineGenerations")
     */
    private $eventLine;

    /**
     * @var EventGenerationDateException[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventGenerationDateException", mappedBy="eventGeneration")
     */
    private $dateExceptions;

    /**
     * @var EventGenerationFrontendUser[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventGenerationFrontendUser", mappedBy="eventGeneration")
     */
    private $frontendUsers;

    /**
     * @var EventGenerationMember[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventGenerationMember", mappedBy="eventGeneration")
     */
    private $members;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="generatedBy")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $generatedEvents;

    public function __construct()
    {
        $this->dateExceptions = new ArrayCollection();
        $this->frontendUsers = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->generatedEvents = new ArrayCollection();
    }

    /**
     * @return Event[]
     */
    public function getGeneratedEvents()
    {
        return $this->generatedEvents;
    }

    /**
     * @return float
     */
    public function getMinimalGapBetweenEvents(): float
    {
        return $this->minimalGapBetweenEvents;
    }

    /**
     * @param float $minimalGapBetweenEvents
     */
    public function setMinimalGapBetweenEvents(float $minimalGapBetweenEvents): void
    {
        $this->minimalGapBetweenEvents = $minimalGapBetweenEvents;
    }

    /**
     * @return string
     */
    public function getCronExpression(): string
    {
        return $this->cronExpression;
    }

    /**
     * @param string $cronExpression
     */
    public function setCronExpression(string $cronExpression): void
    {
        $this->cronExpression = $cronExpression;
    }

    /**
     * @return float
     */
    public function getWeekdayWeight(): float
    {
        return $this->weekdayWeight;
    }

    /**
     * @param float $weekdayWeight
     */
    public function setWeekdayWeight(float $weekdayWeight): void
    {
        $this->weekdayWeight = $weekdayWeight;
    }

    /**
     * @return float
     */
    public function getSaturdayWeight(): float
    {
        return $this->saturdayWeight;
    }

    /**
     * @param float $saturdayWeight
     */
    public function setSaturdayWeight(float $saturdayWeight): void
    {
        $this->saturdayWeight = $saturdayWeight;
    }

    /**
     * @return float
     */
    public function getSundayWeight(): float
    {
        return $this->sundayWeight;
    }

    /**
     * @param float $sundayWeight
     */
    public function setSundayWeight(float $sundayWeight): void
    {
        $this->sundayWeight = $sundayWeight;
    }

    /**
     * @return float
     */
    public function getHolidayWeight(): float
    {
        return $this->holidayWeight;
    }

    /**
     * @param float $holidayWeight
     */
    public function setHolidayWeight(float $holidayWeight): void
    {
        $this->holidayWeight = $holidayWeight;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return EventLine
     */
    public function getEventLine(): EventLine
    {
        return $this->eventLine;
    }

    /**
     * @param EventLine $eventLine
     */
    public function setEventLine(EventLine $eventLine): void
    {
        $this->eventLine = $eventLine;
    }

    /**
     * @return EventGenerationDateException[]|ArrayCollection
     */
    public function getDateExceptions()
    {
        return $this->dateExceptions;
    }

    /**
     * @param EventGenerationDateException[]|ArrayCollection $dateExceptions
     */
    public function setDateExceptions($dateExceptions): void
    {
        $this->dateExceptions = $dateExceptions;
    }

    /**
     * @return EventGenerationFrontendUser[]|ArrayCollection
     */
    public function getFrontendUsers()
    {
        return $this->frontendUsers;
    }

    /**
     * @param EventGenerationFrontendUser[]|ArrayCollection $frontendUsers
     */
    public function setFrontendUsers($frontendUsers): void
    {
        $this->frontendUsers = $frontendUsers;
    }

    /**
     * @return EventGenerationMember[]|ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param EventGenerationMember[]|ArrayCollection $members
     */
    public function setMembers($members): void
    {
        $this->members = $members;
    }
}
