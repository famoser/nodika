<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:46
 */

namespace AppBundle\Entity\Traits;


use Doctrine\ORM\Mapping as ORM;

trait PersonTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="text")
     */
    private $givenName;

    /**
     * @ORM\Column(type="text")
     */
    private $familyName;

    /**
     * @return mixed
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param mixed $jobTitle
     * @return PersonTrait
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    /**
     * Set givenName
     *
     * @param string $givenName
     *
     * @return static
     */
    public function setGivenName($givenName)
    {
        $this->givenName = $givenName;

        return $this;
    }

    /**
     * Get givenName
     *
     * @return string
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * Set familyName
     *
     * @param string $familyName
     *
     * @return static
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * Get familyName
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * get the person identifier
     *
     * @return string
     */
    protected function getPersonIdentifier()
    {
        return $this->jobTitle . " " . $this->getGivenName() . " " . $this->getFamilyName();
    }
}