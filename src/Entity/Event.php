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
use App\Entity\Traits\EventTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Helper\DateTimeFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Event is a time unit which is assigned to a clinic or a person.
 */
#[ORM\Entity(repositoryClass: \App\Repository\EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event extends BaseEntity
{
    use EventTrait;
    use IdTrait;
    use SoftDeleteTrait;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\EventPast>
     */
    #[ORM\OneToMany(targetEntity: \EventPast::class, mappedBy: 'event', cascade: ['all'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private \Doctrine\Common\Collections\Collection $eventPast;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\EventTag>
     */
    #[ORM\JoinTable(name: 'event_event_tags')]
    #[ORM\ManyToMany(targetEntity: EventTag::class)]
    private \Doctrine\Common\Collections\Collection $eventTags;

    #[ORM\ManyToOne(targetEntity: \EventGeneration::class, inversedBy: 'appliedEvents')]
    private ?EventGeneration $generatedBy = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->eventPast = new ArrayCollection();
        $this->eventTags = new ArrayCollection();
    }

    /**
     * @return EventPast[]|ArrayCollection
     */
    public function getEventPast(): \Doctrine\Common\Collections\Collection
    {
        return $this->eventPast;
    }

    /**
     * returns a short representation of start/end datetime of the event.
     */
    public function toShort(): string
    {
        return
            $this->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT).
            ' - '.
            $this->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }

    /**
     * @return EventTag[]|ArrayCollection
     */
    public function getEventTags(): \Doctrine\Common\Collections\Collection
    {
        return $this->eventTags;
    }

    /**
     * @return Event
     */
    public static function create(EventGenerationPreviewEvent $preview): static
    {
        $event = new static();
        $event->writeValues($preview);

        return $event;
    }

    public function getGeneratedBy(): ?EventGeneration
    {
        return $this->generatedBy;
    }

    public function setGeneratedBy(?EventGeneration $generatedBy): void
    {
        $this->generatedBy = $generatedBy;
    }

    /**
     * @return bool
     */
    public function ownedBy(Doctor $doctor)
    {
        if ($this->getDoctor() === $doctor) {
            return true;
        }

        return $doctor->getClinics()->contains($this->getClinic());
    }
}
