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
class EventGenerationTargetClinic extends BaseEntity
{
    use EventGenerationTarget;
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: \Clinic::class)]
    private Clinic $clinic;

    #[ORM\ManyToOne(targetEntity: EventGeneration::class, inversedBy: 'clinics')]
    private EventGeneration $eventGeneration;

    public function getClinic(): Clinic
    {
        return $this->clinic;
    }

    public function setClinic(Clinic $clinic): void
    {
        $this->clinic = $clinic;
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
