<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Event;

use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\EventTag;

class SearchModel
{
    const NONE = 0;
    const MONTH = 1;
    const YEAR = 2;

    /**
     * @var \DateTime
     */
    private $startDateTime;

    /**
     * @var \DateTime
     */
    private $endDateTime;

    /**
     * @var Clinic|null
     */
    private $clinic;

    /**
     * @var Doctor|null
     */
    private $doctor;

    /**
     * @var bool|null
     */
    private $isConfirmed;

    /**
     * @var int
     */
    private $maxResults = 3000;

    /**
     * @var Clinic[]
     */
    private $clinics;

    /**
     * @var EventTag[]
     */
    private $eventTags;

    /**
     * @var bool
     */
    private $invertOrder = false;

    public function __construct($size)
    {
        $this->startDateTime = new \DateTime();

        if (self::MONTH === $size) {
            $this->endDateTime = new \DateTime('now + 1 month');
        } elseif (self::YEAR === $size) {
            $this->endDateTime = new \DateTime('now + 1 year');
        } elseif (self::NONE !== $size) {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @return \DateTime
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTime $startDateTime): void
    {
        $this->startDateTime = $startDateTime;
    }

    public function getEndDateTime(): \DateTime
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(\DateTime $endDateTime): void
    {
        $this->endDateTime = $endDateTime;
    }

    public function getClinic(): ?Clinic
    {
        return $this->clinic;
    }

    public function setClinic(?Clinic $clinic): void
    {
        $this->clinic = $clinic;
    }

    public function getDoctor(): ?Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(?Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(?bool $isConfirmed): void
    {
        $this->isConfirmed = $isConfirmed;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }

    public function isInvertOrder(): bool
    {
        return $this->invertOrder;
    }

    public function setInvertOrder(bool $invertOrder): void
    {
        $this->invertOrder = $invertOrder;
    }

    /**
     * @return Clinic[]
     */
    public function getClinics()
    {
        return $this->clinics;
    }

    /**
     * @param Clinic[] $clinics
     */
    public function setClinics($clinics): void
    {
        $this->clinics = $clinics;
    }

    /**
     * @return EventTag[]
     */
    public function getEventTags()
    {
        return $this->eventTags;
    }

    /**
     * @param EventTag[] $eventTags
     */
    public function setEventTags($eventTags): void
    {
        $this->eventTags = $eventTags;
    }
}
