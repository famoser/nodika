<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait StartEndTrait
{
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $startDateTime = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $endDateTime = null;

    public function getStartDateTime(): ?\DateTime
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTime $startDateTime): void
    {
        $this->startDateTime = $startDateTime;
    }

    public function getEndDateTime(): ?\DateTime
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(\DateTime $endDateTime): void
    {
        $this->endDateTime = $endDateTime;
    }
}
