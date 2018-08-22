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
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var boolean|null
     */
    private $isConfirmed;

    /**
     * @var int
     */
    private $maxResults = 3000;

    /**
     * @var Clinic[]|ArrayCollection
     */
    private $clinics;

    /**
     * @var EventTag[]|ArrayCollection
     */
    private $eventTags;

    /**
     * @var bool
     */
    private $invertOrder = false;

    public function __construct($size)
    {
        $this->startDateTime = new \DateTime();

        if ($size == SearchModel::MONTH) {
            $this->endDateTime = new \DateTime("now + 1 month");
        } elseif ($size == SearchModel::YEAR) {
            $this->endDateTime = new \DateTime("now + 1 year");
        } elseif ($size != SearchModel::NONE) {
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

    /**
     * @param \DateTime $startDateTime
     */
    public function setStartDateTime(\DateTime $startDateTime): void
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndDateTime(): \DateTime
    {
        return $this->endDateTime;
    }

    /**
     * @param \DateTime $endDateTime
     */
    public function setEndDateTime(\DateTime $endDateTime): void
    {
        $this->endDateTime = $endDateTime;
    }

    /**
     * @return Clinic|null
     */
    public function getClinic(): ?Clinic
    {
        return $this->clinic;
    }

    /**
     * @param Clinic|null $clinic
     */
    public function setClinic(?Clinic $clinic): void
    {
        $this->clinic = $clinic;
    }

    /**
     * @return Doctor|null
     */
    public function getDoctor(): ?Doctor
    {
        return $this->doctor;
    }

    /**
     * @param Doctor|null $doctor
     */
    public function setDoctor(?Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    /**
     * @return bool|null
     */
    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    /**
     * @param bool|null $isConfirmed
     */
    public function setIsConfirmed(?bool $isConfirmed): void
    {
        $this->isConfirmed = $isConfirmed;
    }

    /**
     * @return int
     */
    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    /**
     * @param int $maxResults
     */
    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }

    /**
     * @return bool
     */
    public function isInvertOrder(): bool
    {
        return $this->invertOrder;
    }

    /**
     * @param bool $invertOrder
     */
    public function setInvertOrder(bool $invertOrder): void
    {
        $this->invertOrder = $invertOrder;
    }

    /**
     * @return Clinic[]|ArrayCollection
     */
    public function getClinics()
    {
        return $this->clinics;
    }

    /**
     * @param Clinic[]|ArrayCollection $clinics
     */
    public function setClinics($clinics): void
    {
        $this->clinics = $clinics;
    }

    /**
     * @return EventTag[]|ArrayCollection
     */
    public function getEventTags()
    {
        return $this->eventTags;
    }

    /**
     * @param EventTag[]|ArrayCollection $eventTags
     */
    public function setEventTags($eventTags): void
    {
        $this->eventTags = $eventTags;
    }
}
