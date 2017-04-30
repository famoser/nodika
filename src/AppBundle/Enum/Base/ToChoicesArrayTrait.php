<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 05/03/2017
 * Time: 09:02
 */

namespace AppBundle\Enum\Base;


trait ToChoicesArrayTrait
{
    /**
     * returns an array used by the ChoiceType
     *
     * @return array
     */
    public static function toChoicesArray()
    {
        return parent::toChoicesArrayInternal(static::class);
    }
}