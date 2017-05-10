<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 10:18
 */

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

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

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    public static function getThingBuilder(FormBuilderInterface $builder, $defaultArray)
    {
        return static::mapThingFields($builder, $defaultArray);
    }

    /**
     * @param FormBuilderInterface|FormMapper $mapper
     * @param $defaultArray
     * @return FormBuilderInterface|FormMapper
     */
    private static function mapThingFields($mapper, $defaultArray)
    {
        return $mapper
            ->add("name", TextType::class, $defaultArray)
            ->add("description", TextType::class, $defaultArray + ["required" => false]);
    }

    /**
     * @param ThingTrait $source
     */
    public function setThingFieldsFrom($source)
    {
        $this->setName($source->getName());
        $this->setDescription($source->getDescription());
    }
}