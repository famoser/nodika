<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 08/04/2018
 * Time: 20:09
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
     * @param EventGenerationTargetDoctor $doctor
     * @return static
     */
    public static function fromDoctor(EventGenerationTargetDoctor $doctor)
    {
        $new = new static();
        $new->doctor = $doctor;
        return $new;
    }

    /**
     * @param EventGenerationTargetClinic $clinic
     * @return static
     */
    public static function fromClinic(EventGenerationTargetClinic $clinic)
    {
        $new = new static();
        $new->clinic = $clinic;
        return $new;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @return EventGenerationTarget
     */
    public function getTarget()
    {
        if ($this->doctor == null) {
            return $this->clinic;
        }
        return $this->doctor;
    }

    /**
     * @return Doctor|null
     */
    public function getDoctor(): ?Doctor
    {
        if ($this->doctor != null) {
            return $this->doctor->getDoctor();
        }
        return null;
    }

    /**
     * @return Clinic|null
     */
    public function getClinic(): ?Clinic
    {
        if ($this->clinic != null) {
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
     * @param int $count
     */
    public function restrictEventTypeResponsibility($eventType, int $count = 0)
    {
        $this->restrictResponsibilityForEventType[$eventType] = true;
        $this->eventTypeResponsibilities[$eventType] = $count;
        $this->eventTypeResponsibilitiesTaken[$eventType] = 0;
    }

    /**
     * @param $eventType
     * @return bool
     */
    public function canAssumeResponsibility($eventType)
    {
        return !isset($this->restrictResponsibilityForEventType[$eventType]) || !$this->restrictResponsibilityForEventType[$eventType] || $this->eventTypeResponsibilities[$eventType] > $this->eventTypeResponsibilitiesTaken[$eventType];
    }

    /**
     * @param $eventType
     */
    public function assumeResponsibility($eventType)
    {
        if (!isset($this->eventTypeResponsibilitiesTaken[$eventType])) {
            $this->eventTypeResponsibilitiesTaken[$eventType] = 1;
        } else {
            $this->eventTypeResponsibilitiesTaken[$eventType]++;
        }
    }

    /**
     * @param $weights
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
