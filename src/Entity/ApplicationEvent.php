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
use Doctrine\ORM\Mapping as ORM;

/**
 * An ApplicationEvent is an event which saves the actions of the organisation.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\ApplicationEventRepository")
 */
class ApplicationEvent extends BaseEntity
{
    use IdTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $applicationEventType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $occurredAtDateTime;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation")
     */
    private $organisation;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getOrganisation() . ' ' . $this->getApplicationEventType();
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * @return int
     */
    public function getApplicationEventType()
    {
        return $this->applicationEventType;
    }

    /**
     * @param int $applicationEventType
     */
    public function setApplicationEventType($applicationEventType)
    {
        $this->applicationEventType = $applicationEventType;
    }

    /**
     * @return \DateTime
     */
    public function getOccurredAtDateTime()
    {
        return $this->occurredAtDateTime;
    }

    /**
     * @param \DateTime $occurredAtDateTime
     */
    public function setOccurredAtDateTime($occurredAtDateTime)
    {
        $this->occurredAtDateTime = $occurredAtDateTime;
    }
}
