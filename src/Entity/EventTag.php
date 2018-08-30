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
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\ThingTrait;
use App\Enum\EventTagColor;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventTag allows to describe events.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventTag extends BaseEntity
{
    use IdTrait;
    use ThingTrait;
    use SoftDeleteTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $color = EventTagColor::BLUE;

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return int
     */
    public function getColor(): int
    {
        return $this->color;
    }

    public function getColorText(): string
    {
        return EventTagColor::getText($this->color);
    }

    /**
     * @param int $color
     */
    public function setColor(int $color): void
    {
        $this->color = $color;
    }
}
