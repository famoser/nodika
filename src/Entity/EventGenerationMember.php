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
 * An EventGenerationMember specifies additional properties for a member
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventGenerationMember extends BaseEntity
{
    use IdTrait;
    use EventGenerationTarget;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Member")
     */
    private $member;

    /**
     * @var EventGeneration
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventGeneration", inversedBy="members")
     */
    private $eventGeneration;

    /**
     * @return Member
     */
    public function getMember(): Member
    {
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember(Member $member): void
    {
        $this->member = $member;
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
