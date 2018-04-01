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

use App\Helper\DateTimeFormatter;
use Doctrine\ORM\Mapping as ORM;

/*
 * automatically keeps track of creation time & last change time
 */

trait TimeTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastChangedAt;


    /**
     * @ORM\PrePersist()
     */
    public function prePersistTime()
    {
        $this->createdAt = new \DateTime();
        $this->lastChangedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateTime()
    {
        $this->lastChangedAt = new \DateTime();
    }


    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastChangedAt()
    {
        return $this->lastChangedAt;
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        if ($this->createdAt != null) {
            return $this->createdAt->format(DateTimeFormatter::DATE_TIME_FORMAT);
        }
        return get_class($this);
    }
}
