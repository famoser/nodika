<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:42
 */

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/*
 * the id used in the entities
 */
trait IdTrait
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $isRemoved = 0;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function getIsRemoved()
    {
        return $this->isRemoved;
    }

    /**
     * @param boolean $isRemoved
     */
    public function setIsRemoved($isRemoved)
    {
        $this->isRemoved = $isRemoved;
    }
}