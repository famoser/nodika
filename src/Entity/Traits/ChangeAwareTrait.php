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

use App\Entity\Doctor;
use Doctrine\ORM\Mapping as ORM;

/*
 * automatically keeps track of creation time & last change time
 */

trait ChangeAwareTrait
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
     * @var Doctor
     *
     * @ORM\ManyToOne(targetEntity="Doctor")
     */
    private $createdBy;

    /**
     * @var Doctor
     *
     * @ORM\ManyToOne(targetEntity="Doctor")
     */
    private $lastChangedBy;

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

    public function getCreatedBy(): Doctor
    {
        return $this->createdBy;
    }

    public function getLastChangedBy(): Doctor
    {
        return $this->lastChangedBy;
    }

    /**
     * register who has changed the entity.
     */
    public function registerChangeBy(Doctor $doctor)
    {
        if (null === $this->createdBy) {
            $this->createdBy = $doctor;
        }
        $this->lastChangedBy = $doctor;
    }
}
