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
use App\Enum\GenerationStep;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventGeneration is the result of one of the generation algorithms.
 *
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
     * this cron expression specifies when a new event starts
     * https://crontab.guru/.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $startCronExpression = '0 8 * * *';

    /**
     * this cron expression specifies when a new event ends
     * https://crontab.guru/.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $endCronExpression = '0 8 * * *';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $differentiateByEventType = false;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal")
     */
    private $weekdayWeight = 1;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal")
     */
    private $saturdayWeight = 1.2;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal")
     */
    private $sundayWeight = 1.5;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal")
     */
    private $holidayWeight = 2;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $mindPreviousEvents = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $applied = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $step = GenerationStep::SET_START_END;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $conflictBufferInEventMultiples = 1;

    /**
     * @var EventTag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventTag")
     * @ORM\JoinTable(name="event_generation_conflicting_event_tags")
     */
    private $conflictEventTags;

    /**
     * @var EventTag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventTag")
     * @ORM\JoinTable(name="event_generation_assign_event_tags")
     */
    private $assignEventTags;

    /**
     * @var EventGenerationDateException[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventGenerationDateException", mappedBy="eventGeneration", cascade={"persist"}, orphanRemoval=true)
     */
    private $dateExceptions;

    /**
     * @var EventGenerationTargetDoctor[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventGenerationTargetDoctor", mappedBy="eventGeneration", cascade={"persist"}, orphanRemoval=true)
     */
    private $doctors;

    /**
     * @var EventGenerationTargetClinic[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventGenerationTargetClinic", mappedBy="eventGeneration", cascade={"persist"}, orphanRemoval=true)
     */
    private $clinics;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="generatedBy")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $appliedEvents;

    /**
     * @var EventGenerationPreviewEvent[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventGenerationPreviewEvent", mappedBy="generatedBy", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $previewEvents;

    public function __construct()
    {
        $this->dateExceptions = new ArrayCollection();
        $this->doctors = new ArrayCollection();
        $this->clinics = new ArrayCollection();
        $this->appliedEvents = new ArrayCollection();
        $this->conflictEventTags = new ArrayCollection();
        $this->assignEventTags = new ArrayCollection();
        $this->previewEvents = new ArrayCollection();
    }

    /**
     * @return Event[]
     */
    public function getAppliedEvents()
    {
        return $this->appliedEvents;
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
     * @return EventGenerationTargetDoctor[]|ArrayCollection
     */
    public function getDoctors()
    {
        return $this->doctors;
    }

    /**
     * @param EventGenerationTargetDoctor[]|ArrayCollection $doctors
     */
    public function setDoctors($doctors): void
    {
        $this->doctors = $doctors;
    }

    /**
     * @return EventGenerationTargetClinic[]|ArrayCollection
     */
    public function getClinics()
    {
        return $this->clinics;
    }

    /**
     * @param EventGenerationTargetClinic[]|ArrayCollection $clinics
     */
    public function setClinics($clinics): void
    {
        $this->clinics = $clinics;
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

    /**
     * @return EventTag[]|ArrayCollection
     */
    public function getAssignEventTags()
    {
        return $this->assignEventTags;
    }

    /**
     * @return bool
     */
    public function getIsApplied(): bool
    {
        return $this->applied;
    }

    /**
     * @param bool $applied
     */
    public function setIsApplied(bool $applied): void
    {
        $this->applied = $applied;
    }

    /**
     * @return int
     */
    public function getConflictBufferInEventMultiples(): int
    {
        return $this->conflictBufferInEventMultiples;
    }

    /**
     * @param int $conflictBufferInEventMultiples
     */
    public function setConflictBufferInEventMultiples(int $conflictBufferInEventMultiples): void
    {
        $this->conflictBufferInEventMultiples = $conflictBufferInEventMultiples;
    }

    /**
     * @return EventGenerationPreviewEvent[]|ArrayCollection
     */
    public function getPreviewEvents()
    {
        return $this->previewEvents;
    }
}
