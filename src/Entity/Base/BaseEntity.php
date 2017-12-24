<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * returns a string representation of this entity.
     *
     * @return string
     */
    abstract public function getFullIdentifier();

    /**
     * @return string
     */
    protected function getTranslationDomainPrefix()
    {
        return 'entity';
    }

    /**
     * returns the builder with all flat fields from the entity.
     *
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     *
     * @return FormBuilderInterface
     */
    protected function getBuilder(FormBuilderInterface $builder, $defaultArray)
    {
        return $builder;
    }

    /**
     * returns the builder with all fields from the entity.
     *
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     *
     * @return mixed
     */
    public static function getBuilderStatic(FormBuilderInterface $builder, $defaultArray = [])
    {
        $instance = new static();

        return $instance->getBuilder($builder, $defaultArray);
    }
}
