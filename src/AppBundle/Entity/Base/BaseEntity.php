<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 05/03/2017
 * Time: 09:26
 */

namespace AppBundle\Entity\Base;

abstract class BaseEntity
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullIdentifier();
    }

    /**
     * returns a string representation of this entity
     *
     * @return string
     */
    public abstract function getFullIdentifier();
}