<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventGeneration;

use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\EventGenerationTargetClinic;
use App\Entity\EventGenerationTargetDoctor;
use App\Entity\Traits\EventGenerationTarget;
use App\Enum\EventType;

class EventTarget
{
    const NONE_IDENTIFIER = 0;
    private static $nextIdentifier = 1;

    /**
     * @var int
     */
    private $identifier;

    /**
     * @var EventGenerationTargetDoctor|null
     */
    private $doctor;

    /**
     * @var EventGenerationTargetClinic|null
     */
    private $clinic;

    public function __construct()
    {
        $this->identifier = static::$nextIdentifier++;
    }

    /**
     * @return static
     */
    public static function fromDoctor(EventGenerationTargetDoctor $doctor)
    {
        $new = new static();
        $new->doctor = $doctor;

        return $new;
    }

    /**
     * @return static
     */
    public static function fromClinic(EventGenerationTargetClinic $clinic)
    {
        $new = new static();
        $new->clinic = $clinic;

        return $new;
    }

    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @return EventGenerationTarget
     */
    public function getTarget()
    {
        if (null === $this->doctor) {
            return $this->clinic;
        }

        return $this->doctor;
    }

    public function getDoctor(): ?Doctor
    {
        if (null !== $this->doctor) {
            return $this->doctor->getDoctor();
        }

        return null;
    }

    public function getClinic(): ?Clinic
    {
        if (null !== $this->clinic) {
            return $this->clinic->getClinic();
        }

        return null;
    }

    /** @var bool */
    private $restrictResponsibilityForEventType = [];

    /** @var int[] */
    private $eventTypeResponsibilities = [];

    /** @var int[] */
    private $eventTypeResponsibilitiesTaken = [];

    /**
     * @param $eventType
     */
    public function restrictEventTypeResponsibility($eventType, int $count = 0)
    {
        $this->restrictResponsibilityForEventType[$eventType] = true;
        $this->eventTypeResponsibilities[$eventType] = $count;
        $this->eventTypeResponsibilitiesTaken[$eventType] = 0;
    }

    /**
     * @param $eventType
     *
     * @return bool
     */
    public function canAssumeResponsibility($eventType)
    {
        return
            !isset($this->restrictResponsibilityForEventType[$eventType]) ||
            !$this->restrictResponsibilityForEventType[$eventType] ||
            $this->eventTypeResponsibilities[$eventType] > $this->eventTypeResponsibilitiesTaken[$eventType];
    }

    /**
     * @param $eventType
     */
    public function assumeResponsibility($eventType)
    {
        if (!isset($this->eventTypeResponsibilitiesTaken[$eventType])) {
            $this->eventTypeResponsibilitiesTaken[$eventType] = 1;
        } else {
            ++$this->eventTypeResponsibilitiesTaken[$eventType];
        }
    }

    /**
     * @param $weights
     *
     * @return float|int
     */
    public function calculateResponsibility($weights)
    {
        $res = 0;
        foreach ($weights as $eventType => $weight) {
            if (isset($this->eventTypeResponsibilitiesTaken[$eventType])) {
                $res += $this->eventTypeResponsibilitiesTaken[$eventType] * $weight;
            }
        }

        return $res;
    }
}
