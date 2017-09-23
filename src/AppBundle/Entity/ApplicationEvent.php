<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;


/**
 * An ApplicationEvent is an event which saves the actions of the organisation
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ApplicationEventRepository")
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organisation")
     */
    private $organisation;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getOrganisation() . " " . $this->getApplicationEventType();
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
