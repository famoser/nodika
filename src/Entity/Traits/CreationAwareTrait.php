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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/*
 * automatically keeps track of creation time & last change time
 */

trait CreationAwareTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Doctor::class)]
    private ?Doctor $createdBy = null;

    #[ORM\PrePersist]
    public function prePersistTime(): void
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getCreatedBy(): Doctor
    {
        return $this->createdBy;
    }

    /**
     * register who has changed the entity.
     */
    public function setCreatedBy(Doctor $doctor): void
    {
        $this->createdBy = $doctor;
    }
}
