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
 *
 * @ORM\HasLifecycleCallbacks
 */
class EventGeneration extends BaseEntity
{
    use ChangeAwareTrait;
    use IdTrait;
    use StartEndTrait;
    use ThingTrait;

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
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $conflictBufferInEventMultiples = 1;

    /**
     * @var EventTag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventTag")
     *
     * @ORM\JoinTable(name="event_generation_conflicting_event_tags")
     */
    private $conflictEventTags;

    /**
     * @var EventTag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventTag")
     *
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
     *
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $appliedEvents;

    /**
     * @var EventGenerationPreviewEvent[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventGenerationPreviewEvent", mappedBy="generatedBy", cascade={"persist"}, orphanRemoval=true)
     *
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

    public function getStartCronExpression(): string
    {
        return $this->startCronExpression;
    }

    public function setStartCronExpression(string $startCronExpression): void
    {
        $this->startCronExpression = $startCronExpression;
    }

    public function getEndCronExpression(): string
    {
        return $this->endCronExpression;
    }

    public function setEndCronExpression(string $endCronExpression): void
    {
        $this->endCronExpression = $endCronExpression;
    }

    public function getWeekdayWeight(): float
    {
        return $this->weekdayWeight;
    }

    public function setWeekdayWeight(float $weekdayWeight): void
    {
        $this->weekdayWeight = $weekdayWeight;
    }

    public function getSaturdayWeight(): float
    {
        return $this->saturdayWeight;
    }

    public function setSaturdayWeight(float $saturdayWeight): void
    {
        $this->saturdayWeight = $saturdayWeight;
    }

    public function getSundayWeight(): float
    {
        return $this->sundayWeight;
    }

    public function setSundayWeight(float $sundayWeight): void
    {
        $this->sundayWeight = $sundayWeight;
    }

    public function getHolidayWeight(): float
    {
        return $this->holidayWeight;
    }

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

    public function getMindPreviousEvents(): bool
    {
        return $this->mindPreviousEvents;
    }

    public function setMindPreviousEvents(bool $mindPreviousEvents): void
    {
        $this->mindPreviousEvents = $mindPreviousEvents;
    }

    public function getDifferentiateByEventType(): bool
    {
        return $this->differentiateByEventType;
    }

    public function setDifferentiateByEventType(bool $differentiateByEventType): void
    {
        $this->differentiateByEventType = $differentiateByEventType;
    }

    public function getStep(): int
    {
        return $this->step;
    }

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

    public function getIsApplied(): bool
    {
        return $this->applied;
    }

    public function setIsApplied(bool $applied): void
    {
        $this->applied = $applied;
    }

    public function getConflictBufferInEventMultiples(): float
    {
        return $this->conflictBufferInEventMultiples;
    }

    public function setConflictBufferInEventMultiples(float $conflictBufferInEventMultiples): void
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
