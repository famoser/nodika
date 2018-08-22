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
 * An EventGenerationClinic specifies additional properties for a clinic
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventGenerationTargetDoctor extends BaseEntity
{
    use IdTrait;
    use EventGenerationTarget;

    /**
     * @var Doctor
     *
     * @ORM\ManyToOne(targetEntity="Doctor")
     */
    private $doctor;

    /**
     * @var EventGeneration
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventGeneration", inversedBy="doctors")
     */
    private $eventGeneration;

    /**
     * @return Doctor
     */
    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }

    /**
     * @param Doctor $doctor
     */
    public function setDoctor(Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    /**
     * @return EventGeneration
     */
    public function getEventGeneration(): EventGeneration
    {
        return $this->eventGeneration;
    }

    /**
     * @param EventGeneration $eventGeneration
     */
    public function setEventGeneration(EventGeneration $eventGeneration): void
    {
        $this->eventGeneration = $eventGeneration;
    }
}
