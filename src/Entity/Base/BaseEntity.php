<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 05/03/2017
 * Time: 09:26
 */

namespace App\Entity\Base;

use App\Framework\TranslatableObject;
use Symfony\Component\Form\FormBuilderInterface;

abstract class BaseEntity extends TranslatableObject
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
    abstract public function getFullIdentifier();

    /**
     * @return string
     */
    protected function getTranslationDomainPrefix()
    {
        return "entity";
    }

    /**
     * returns the builder with all flat fields from the entity
     *
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    protected function getBuilder(FormBuilderInterface $builder, $defaultArray)
    {
        return $builder;
    }

    /**
     * returns the builder with all fields from the entity
     *
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return mixed
     */
    public static function getBuilderStatic(FormBuilderInterface $builder, $defaultArray = [])
    {
        $instance = new static();
        return $instance->getBuilder($builder, $defaultArray);
    }
}
