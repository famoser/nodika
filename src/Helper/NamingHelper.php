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
     */
    public static function classToTranslationDomain($classWithNamespace): string
    {
        $className = mb_substr($classWithNamespace, mb_strrpos($classWithNamespace, '\\') + 1);

        return static::camelCaseToTranslation($className);
    }

    /**
     * makes from camelCase => camel_case.
     */
    private static function camelCaseToTranslation($camelCase): string
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $camelCase));
    }

    /**
     * produces App\Form\MyClassName\MyClassNameType from Famoser\Class\MyClassName
     * if $isRemoveType is true then the remove form is returned.
     *
     * @param string $classWithNamespace
     * @param bool   $isRemoveType
     */
    public static function classToCrudFormType($classWithNamespace, $isRemoveType): string
    {
        $prepend = $isRemoveType ? 'Remove' : '';
        $className = mb_substr($classWithNamespace, mb_strrpos($classWithNamespace, '\\') + 1);

        return 'App\\Form\\'.$className.'\\'.$prepend.$className.'Type';
    }

    /**
     * produces my_constant from MY_CONSTANT.
     */
    public static function constantToTranslation($constant): string
    {
        return mb_strtolower($constant);
    }

    /**
     * the property to be converted to a array for the builder including the label clinic.
     */
    public static function propertyToTranslationForBuilder($propertyName): array
    {
        return ['label' => static::propertyToTranslation($propertyName)];
    }

    /**
     * the property to be converted to a label.
     */
    public static function propertyToTranslation($propertyName): string
    {
        return static::camelCaseToTranslation($propertyName);
    }

    /**
     * the name of the trait to be converted to a array for the builder including the label clinic.
     *
     * @return string[]
     */
    public static function traitNameToTranslationForBuilder($trait): array
    {
        return ['label' => 'trait.name', 'translation_domain' => static::traitToTranslationDomain($trait)];
    }

    /**
     * produces my_stuff from Famoser\Class\MyStuffTrait.
     */
    public static function traitToTranslationDomain($traitWithNamespace): string
    {
        $traitName = mb_substr($traitWithNamespace, mb_strrpos($traitWithNamespace, '\\') + 1);

        // subtract 5 because strlen("Trait") == 5
        return 'trait_'.static::camelCaseToTranslation(mb_substr($traitName, 0, -5));
    }
}
