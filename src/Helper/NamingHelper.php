<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

class NamingHelper
{
    /**
     * produces my_class_name from Famoser\Class\MyClassName.
     *
     * @param $classWithNamespace
     *
     * @return string
     */
    public static function classToTranslationDomain($classWithNamespace)
    {
        $className = mb_substr($classWithNamespace, mb_strrpos($classWithNamespace, '\\') + 1);

        return static::camelCaseToTranslation($className);
    }

    /**
     * produces App\Form\MyClassName\MyClassNameType from Famoser\Class\MyClassName
     * if $isRemoveType is true then the remove form is returned.
     *
     * @param string $classWithNamespace
     * @param bool   $isRemoveType
     *
     * @return string
     */
    public static function classToCrudFormType($classWithNamespace, $isRemoveType)
    {
        $prepend = $isRemoveType ? 'Remove' : '';
        $className = mb_substr($classWithNamespace, mb_strrpos($classWithNamespace, '\\') + 1);

        return 'App\\Form\\'.$className.'\\'.$prepend.$className.'Type';
    }

    /**
     * produces my_stuff from Famoser\Class\MyStuffTrait.
     *
     * @param $traitWithNamespace
     *
     * @return string
     */
    public static function traitToTranslationDomain($traitWithNamespace)
    {
        $traitName = mb_substr($traitWithNamespace, mb_strrpos($traitWithNamespace, '\\') + 1);
        // subtract 5 because strlen("Trait") == 5
        return 'trait_'.static::camelCaseToTranslation(mb_substr($traitName, 0, -5));
    }

    /**
     * produces my_constant from MY_CONSTANT.
     *
     * @param $constant
     *
     * @return string
     */
    public static function constantToTranslation($constant)
    {
        return mb_strtolower($constant);
    }

    /**
     * the property to be converted to a label.
     *
     * @param $propertyName
     *
     * @return string
     */
    public static function propertyToTranslation($propertyName)
    {
        return static::camelCaseToTranslation($propertyName);
    }

    /**
     * the property to be converted to a array for the builder including the label member.
     *
     * @param $propertyName
     *
     * @return array
     */
    public static function propertyToTranslationForBuilder($propertyName)
    {
        return ['label' => static::propertyToTranslation($propertyName)];
    }

    /**
     * makes from camelCase => camel_case.
     *
     * @param $camelCase
     *
     * @return string
     */
    private static function camelCaseToTranslation($camelCase)
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $camelCase));
    }

    /**
     * the name of the trait to be converted to a array for the builder including the label member.
     *
     * @param $trait
     *
     * @return string[]
     */
    public static function traitNameToTranslationForBuilder($trait)
    {
        return ['label' => 'trait.name', 'translation_domain' => static::traitToTranslationDomain($trait)];
    }
}
