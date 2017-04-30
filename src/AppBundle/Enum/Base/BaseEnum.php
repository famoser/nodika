<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 05/03/2017
 * Time: 08:41
 */

namespace AppBundle\Enum\Base;

use ReflectionClass;

abstract class BaseEnum
{
    protected static function toChoicesArrayInternal($parentClass)
    {
        $reflection = new ReflectionClass($parentClass);
        $instance = $reflection->newInstanceWithoutConstructor();

        $choices = $reflection->getConstants();
        $toString = $reflection->getMethod('toString');

        $res = [];
        foreach ($choices as $choice) {
            $res[$toString->invoke($instance, $choice)] = $choice;
        }
        return $res;
    }

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public abstract function toString($enumValue);



    /**
     * returns an array used by the ChoiceType
     *
     * @return array
     */
    /* PASE THIS IN PARENT CLASSES
    public static function toChoicesArray()
    {
        return parent::toChoicesArrayInternal(static::class);
    }
    */
}