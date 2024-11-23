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

trait PersonTrait
{
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $jobTitle = null;

    #[ORM\Column(type: 'text')]
    private ?string $givenName = null;

    #[ORM\Column(type: 'text')]
    private ?string $familyName = null;

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    public function setJobTitle(string $jobTitle): void
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * Get givenName.
     *
     * @return string
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * Set givenName.
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
     * Get familyName.
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Set familyName.
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

    public function getFullName(): string
    {
        return $this->getGivenName().' '.$this->getFamilyName();
    }
}
