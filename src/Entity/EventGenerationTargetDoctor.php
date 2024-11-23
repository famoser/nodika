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
use App\Entity\Traits\EventGenerationTarget;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventGenerationClinic specifies additional properties for a clinic.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class EventGenerationTargetDoctor extends BaseEntity
{
    use EventGenerationTarget;
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: \Doctor::class)]
    private Doctor $doctor;

    #[ORM\ManyToOne(targetEntity: EventGeneration::class, inversedBy: 'doctors')]
    private EventGeneration $eventGeneration;

    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    public function getEventGeneration(): EventGeneration
    {
        return $this->eventGeneration;
    }

    public function setEventGeneration(EventGeneration $eventGeneration): void
    {
        $this->eventGeneration = $eventGeneration;
    }
}
