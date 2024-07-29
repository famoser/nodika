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
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\StartEndTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventGeneration is the result of one of the generation algorithms.
 *
 * @ORM\Entity()
 *
 * @ORM\HasLifecycleCallbacks
 */
class EventGenerationDateException extends BaseEntity
{
    use IdTrait;
    use StartEndTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eventType;

    public function getEventType(): ?int
    {
        return $this->eventType;
    }

    public function setEventType(?int $eventType): void
    {
        $this->eventType = $eventType;
    }

    /**
     * @var EventGeneration
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventGeneration", inversedBy="dateExceptions")
     */
    private $eventGeneration;

    public function getEventGeneration(): EventGeneration
    {
        return $this->eventGeneration;
    }

    public function setEventGeneration(EventGeneration $eventGeneration): void
    {
        $this->eventGeneration = $eventGeneration;
    }
}
