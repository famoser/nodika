<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 10:18
 */

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/*
 * represents a Thing; an object with name & optional description
 */
trait ThingTrait
{

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return static
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * returns the name of this thing
     *
     * @return string
     */
    public function getThingIdentifier()
    {
        return $this->getName();
    }
}