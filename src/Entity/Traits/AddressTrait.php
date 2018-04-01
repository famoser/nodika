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
use Symfony\Component\Validator\Constraints as Assert;

/*
 * Address information
 */

trait AddressTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $streetNr;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $addressLine;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Country()
     */
    private $country = 'CH';

    /**
     * Get street.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street.
     *
     * @param string $street
     *
     * @return static
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get streetNr.
     *
     * @return string
     */
    public function getStreetNr()
    {
        return $this->streetNr;
    }

    /**
     * Set streetNr.
     *
     * @param string $streetNr
     *
     * @return static
     */
    public function setStreetNr($streetNr)
    {
        $this->streetNr = $streetNr;

        return $this;
    }

    /**
     * Get addressLine.
     *
     * @return string
     */
    public function getAddressLine()
    {
        return $this->addressLine;
    }

    /**
     * Set addressLine.
     *
     * @param string $addressLine
     *
     * @return static
     */
    public function setAddressLine($addressLine)
    {
        $this->addressLine = $addressLine;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return int
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set postalCode.
     *
     * @param int $postalCode
     *
     * @return static
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get addressRegion.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set addressRegion.
     *
     * @param string $city
     *
     * @return static
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return static
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * returns all non-empty address lines.
     *
     * @return string[]
     */
    public function getAddressLines()
    {
        $res = [];
        $lineOne = $this->getStreet();
        if (mb_strlen($lineOne) > 0 && mb_strlen($this->getStreetNr()) > 0) {
            $lineOne .= ' ' . $this->getStreetNr();
        }
        if (mb_strlen($lineOne) > 0) {
            $res[] = $lineOne;
        }
        if (mb_strlen($this->getAddressLine()) > 0) {
            $res[] = $this->getAddressLine();
        }
        $line3 = $this->getPostalCode();
        if (mb_strlen($line3) > 0 && mb_strlen($this->getCity() > 0)) {
            $line3 .= ' ' . $this->getCity();
        }
        if (mb_strlen($line3) > 0) {
            $res[] = $line3;
        }
        if (mb_strlen($this->getCountry()) > 0) {
            $res[] = $this->getCountry();
        }

        return $res;
    }
}
