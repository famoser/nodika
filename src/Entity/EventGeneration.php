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
use App\Enum\GenerationStatus;
use App\Enum\GenerationStep;
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
     * this cron expression specifies when a new event starts
     * https://crontab.guru/
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $startCronExpression = "* 8 * * *";

    /**
     * this cron expression specifies when a new event ends
     * https://crontab.guru/
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $endCronExpression = "* 8 * * *";

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $differentiateByEventType;

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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $mindPreviousEvents = true;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status = GenerationStatus::STARTED;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $step = GenerationStep::CHOOSE_TARGETS;

    /**
     * @var EventTag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventTag")
     * @ORM\JoinTable(name="event_generation_event_tags")
     */
    private $conflictEventTags;

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
        $this->conflictEventTags = new ArrayCollection();
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
    public function getStartCronExpression(): string
    {
        return $this->startCronExpression;
    }

    /**
     * @param string $startCronExpression
     */
    public function setStartCronExpression(string $startCronExpression): void
    {
        $this->startCronExpression = $startCronExpression;
    }

    /**
     * @return string
     */
    public function getEndCronExpression(): string
    {
        return $this->endCronExpression;
    }

    /**
     * @param string $endCronExpression
     */
    public function setEndCronExpression(string $endCronExpression): void
    {
        $this->endCronExpression = $endCronExpression;
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

    /**
     * @return bool
     */
    public function getMindPreviousEvents(): bool
    {
        return $this->mindPreviousEvents;
    }

    /**
     * @param bool $mindPreviousEvents
     */
    public function setMindPreviousEvents(bool $mindPreviousEvents): void
    {
        $this->mindPreviousEvents = $mindPreviousEvents;
    }

    /**
     * @return bool
     */
    public function getDifferentiateByEventType(): bool
    {
        return $this->differentiateByEventType;
    }

    /**
     * @param bool $differentiateByEventType
     */
    public function setDifferentiateByEventType(bool $differentiateByEventType): void
    {
        $this->differentiateByEventType = $differentiateByEventType;
    }

    /**
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * @param int $step
     */
    public function setStep(int $step): void
    {
        $this->step = $step;
    }

    /**
     * @return EventTag[]|ArrayCollection
     */
    public function getConflictEventTags()
    {
        return $this->conflictEventTags;
    }
}
