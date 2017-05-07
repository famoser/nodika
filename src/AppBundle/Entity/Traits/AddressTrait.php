<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:46
 */

namespace AppBundle\Entity\Traits;


use Doctrine\ORM\Mapping as ORM;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

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
     */
    private $country = "CH";


    /**
     * Set street
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
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set addressLine
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
     * Get addressLine
     *
     * @return string
     */
    public function getAddressLine()
    {
        return $this->addressLine;
    }

    /**
     * Set streetNr
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
     * Get streetNr
     *
     * @return string
     */
    public function getStreetNr()
    {
        return $this->streetNr;
    }

    /**
     * Set postalCode
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
     * Get postalCode
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set addressRegion
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
     * Get addressRegion
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
     * @return static
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * returns all non-empty address lines
     *
     * @return string[]
     */
    public function getAddressLines()
    {
        $res = [];
        $lineOne = $this->getStreet();
        if ($lineOne != "" && $this->getStreetNr() != "") {
            $lineOne .= " " . $this->getStreetNr();
        }
        if ($lineOne != "") {
            $res[] = $lineOne;
        }
        if ($this->getAddressLine() != "") {
            $res[] = $this->getAddressLine();
        }
        $line3 = $this->getPostalCode();
        if ($line3 != "" && $this->getCity() != "") {
            $line3 .= " " . $this->getCity();
        }
        if ($line3 != "") {
            $res[] = $line3;
        }
        if ($this->getCountry() != "") {
            $res[] = $this->getCountry();
        }
        return $res;
    }

    /**
     * gets the street identifier
     *
     * @return string
     */
    protected function getAddressIdentifier()
    {
        return implode(", ", $this->getAddressLine());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    public static function getAddressBuilder(FormBuilderInterface $builder, $defaultArray)
    {
        return static::mapAddressFields($builder, $defaultArray);
    }

    /**
     * @param FormBuilderInterface|FormMapper $mapper
     * @param $defaultArray
     * @return FormBuilderInterface|FormMapper
     */
    private static function mapAddressFields($mapper, $defaultArray)
    {
        return $mapper
            ->add("street", TextType::class, $defaultArray)
            ->add("streetNr", NumberType::class, $defaultArray)
            ->add("addressLine", TextType::class, $defaultArray + ["required" => false])
            ->add("postalCode", NumberType::class, $defaultArray)
            ->add("city", TextType::class, $defaultArray)
            ->add("country", CountryType::class, $defaultArray);
    }
}