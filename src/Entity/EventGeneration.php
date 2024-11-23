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
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class EventGeneration extends BaseEntity
{
    use ChangeAwareTrait;
    use IdTrait;
    use StartEndTrait;
    use ThingTrait;

    /**
     * this cron expression specifies when a new event starts
     * https://crontab.guru/.
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private string $startCronExpression = '0 8 * * *';

    /**
     * this cron expression specifies when a new event ends
     * https://crontab.guru/.
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private string $endCronExpression = '0 8 * * *';

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $differentiateByEventType = false;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DECIMAL)]
    private ?string $weekdayWeight = 1;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DECIMAL)]
    private ?string $saturdayWeight = 1.2;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DECIMAL)]
    private ?string $sundayWeight = 1.5;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DECIMAL)]
    private ?string $holidayWeight = 2;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $mindPreviousEvents = true;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $applied = false;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $step = GenerationStep::SET_START_END;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::FLOAT)]
    private ?float $conflictBufferInEventMultiples = 1;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\EventTag>
     */
    #[ORM\JoinTable(name: 'event_generation_conflicting_event_tags')]
    #[ORM\ManyToMany(targetEntity: EventTag::class)]
    private \Doctrine\Common\Collections\Collection $conflictEventTags;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\EventTag>
     */
    #[ORM\JoinTable(name: 'event_generation_assign_event_tags')]
    #[ORM\ManyToMany(targetEntity: EventTag::class)]
    private \Doctrine\Common\Collections\Collection $assignEventTags;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\EventGenerationDateException>
     */
    #[ORM\OneToMany(targetEntity: EventGenerationDateException::class, mappedBy: 'eventGeneration', cascade: ['persist'], orphanRemoval: true)]
    private \Doctrine\Common\Collections\Collection $dateExceptions;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\EventGenerationTargetDoctor>
     */
    #[ORM\OneToMany(targetEntity: \EventGenerationTargetDoctor::class, mappedBy: 'eventGeneration', cascade: ['persist'], orphanRemoval: true)]
    private \Doctrine\Common\Collections\Collection $doctors;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\EventGenerationTargetClinic>
     */
    #[ORM\OneToMany(targetEntity: \EventGenerationTargetClinic::class, mappedBy: 'eventGeneration', cascade: ['persist'], orphanRemoval: true)]
    private \Doctrine\Common\Collections\Collection $clinics;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Event>
     */
    #[ORM\OneToMany(targetEntity: \Event::class, mappedBy: 'generatedBy')]
    #[ORM\OrderBy(['startDateTime' => 'ASC'])]
    private \Doctrine\Common\Collections\Collection $appliedEvents;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\EventGenerationPreviewEvent>
     */
    #[ORM\OneToMany(targetEntity: \EventGenerationPreviewEvent::class, mappedBy: 'generatedBy', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['startDateTime' => 'ASC'])]
    private \Doctrine\Common\Collections\Collection $previewEvents;

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
    public function getAppliedEvents(): \Doctrine\Common\Collections\Collection
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
    public function getDateExceptions(): \Doctrine\Common\Collections\Collection
    {
        return $this->dateExceptions;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \App\Entity\EventGenerationDateException> $dateExceptions
     */
    public function setDateExceptions(\Doctrine\Common\Collections\Collection $dateExceptions): void
    {
        $this->dateExceptions = $dateExceptions;
    }

    /**
     * @return EventGenerationTargetDoctor[]|ArrayCollection
     */
    public function getDoctors(): \Doctrine\Common\Collections\Collection
    {
        return $this->doctors;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \App\Entity\EventGenerationTargetDoctor> $doctors
     */
    public function setDoctors(\Doctrine\Common\Collections\Collection $doctors): void
    {
        $this->doctors = $doctors;
    }

    /**
     * @return EventGenerationTargetClinic[]|ArrayCollection
     */
    public function getClinics(): \Doctrine\Common\Collections\Collection
    {
        return $this->clinics;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \App\Entity\EventGenerationTargetClinic> $clinics
     */
    public function setClinics(\Doctrine\Common\Collections\Collection $clinics): void
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
    public function getConflictEventTags(): \Doctrine\Common\Collections\Collection
    {
        return $this->conflictEventTags;
    }

    /**
     * @return EventTag[]|ArrayCollection
     */
    public function getAssignEventTags(): \Doctrine\Common\Collections\Collection
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
    public function getPreviewEvents(): \Doctrine\Common\Collections\Collection
    {
        return $this->previewEvents;
    }
}
