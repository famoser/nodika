<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:46
 */

namespace AppBundle\Entity\Traits;


use AppBundle\Helper\NamingHelper;
use Doctrine\ORM\Mapping as ORM;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

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

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    public static function getPersonBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ["translation_domain" => NamingHelper::traitToTranslationDomain(PersonTrait::class)] + $defaultArray;
        $builder->add(
            "jobTitle",
            TextType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("jobTitle") + ["required" => false]
        );
        $builder->add(
            "givenName",
            TextType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("givenName")
        );
        $builder->add(
            "familyName",
            TextType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("familyName")
        );
        return $builder;
    }

    /**
     * @param PersonTrait $source
     */
    public function setPersonFieldsFrom($source)
    {
        $this->setJobTitle($source->getJobTitle());
        $this->setGivenName($source->getGivenName());
        $this->setFamilyName($source->getFamilyName());
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $res = $this->getJobTitle();
        if ($this->getJobTitle() != "") {
            $res .= " ";
        }
        return $res . $this->getGivenName() . " " . $this->getFamilyName();
    }
}