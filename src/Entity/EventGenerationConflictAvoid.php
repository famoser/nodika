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
use App\Entity\Traits\EventGenerationTarget;
use App\Entity\Traits\IdTrait;
use App\Enum\DistributionType;
use App\Helper\DateTimeFormatter;
use App\Model\EventLineGeneration\Base\BaseConfiguration;
use App\Model\EventLineGeneration\Base\BaseOutput;
use App\Model\EventLineGeneration\GenerationResult;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventGenerationMember specifies additional properties for a member
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventGenerationConflictAvoid extends BaseEntity
{
    use IdTrait;

    /**
     * @var FrontendUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventLine")
     */
    private $eventLine;

    /**
     * @var EventGeneration
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventGeneration", inversedBy="conflictPrevents")
     */
    private $eventGeneration;

    /**
     * @return FrontendUser
     */
    public function getEventLine(): FrontendUser
    {
        return $this->eventLine;
    }

    /**
     * @param FrontendUser $eventLine
     */
    public function setEventLine(FrontendUser $eventLine): void
    {
        $this->eventLine = $eventLine;
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
