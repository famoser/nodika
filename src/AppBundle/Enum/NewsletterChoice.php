<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 16:05
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\ToChoicesArrayTrait;

class NewsletterChoice extends BaseEnum
{
    const REGISTER = 1;
    const REGISTER_INFO_ONLY = 2;
    const QUESTION = 3;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::REGISTER:
                return "newsletter_choice.register";
            case static::REGISTER_INFO_ONLY:
                return "newsletter_choice.register_info_only";
            case static::QUESTION:
                return "newsletter_choice.question";
            default:
                return "enum.unknown";
        }
    }

    use ToChoicesArrayTrait;
}