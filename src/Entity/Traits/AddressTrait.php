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

use App\Helper\NamingHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * Address information
 */

trait AddressTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $street;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $streetNr;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $addressLine;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Country()
     */
    private $country = 'CH';

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
     * Get street.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
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
     * Get addressLine.
     *
     * @return string
     */
    public function getAddressLine()
    {
        return $this->addressLine;
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
     * Get streetNr.
     *
     * @return string
     */
    public function getStreetNr()
    {
        return $this->streetNr;
    }

    /**
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return static
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
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
     * Get addressRegion.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
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
        if ('' !== $lineOne && '' !== $this->getStreetNr()) {
            $lineOne .= ' '.$this->getStreetNr();
        }
        if ('' !== $lineOne) {
            $res[] = $lineOne;
        }
        if ('' !== $this->getAddressLine()) {
            $res[] = $this->getAddressLine();
        }
        $line3 = $this->getPostalCode();
        if ('' !== $line3 && '' !== $this->getCity()) {
            $line3 .= ' '.$this->getCity();
        }
        if ('' !== $line3) {
            $res[] = $line3;
        }
        if ('' !== $this->getCountry()) {
            $res[] = $this->getCountry();
        }

        return $res;
    }

    /**
     * gets the street identifier.
     *
     * @return string
     */
    protected function getAddressIdentifier()
    {
        return implode(', ', $this->getAddressLines());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     *
     * @return FormBuilderInterface
     */
    public static function getAddressBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ['translation_domain' => NamingHelper::traitToTranslationDomain(AddressTrait::class)] + $defaultArray;
        $builder->add(
            'street',
            TextType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('street')
        );
        $builder->add(
            'streetNr',
            TextType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('streetNr')
        );
        $builder->add(
            'addressLine',
            TextType::class,
            ['required' => false] + $builderArray + NamingHelper::propertyToTranslationForBuilder('addressLine')
        );
        $builder->add(
            'postalCode',
            NumberType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('postalCode')
        );
        $builder->add(
            'city',
            TextType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('city')
        );
        $builder->add(
            'country',
            TextType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('country')
        );

        return $builder;
    }

    /**
     * @param AddressTrait $source
     */
    public function setAddressFieldsFrom($source)
    {
        $this->setStreet($source->getStreet());
        $this->setStreetNr($source->getStreetNr());
        $this->setAddressLine($source->getAddressLine());
        $this->setPostalCode($source->getPostalCode());
        $this->setCity($source->getCity());
        $this->setCountry($source->getCountry());
    }
}
