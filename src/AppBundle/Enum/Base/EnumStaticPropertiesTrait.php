<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 05/03/2017
 * Time: 09:02
 */

namespace AppBundle\Enum\Base;


trait EnumStaticPropertiesTrait
{
    /**
     * returns an array fit to be used by the ChoiceType
     *
     * @return array
     */
    public static function getChoicesForBuilder()
    {
        $elem = new static();
        return $elem->getChoicesForBuilderInternal();
    }

    /**
     * translate enum value
     *
     * @param $enumValue
     * @return array
     */
    public static function getTranslationForBuilder($enumValue)
    {
        $elem = new static();
        return $elem->getTranslationForBuilderInternal($enumValue);
    }
}