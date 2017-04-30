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
    const QUESTION = 2;

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
                return "register";
            case static::QUESTION:
                return "question";
            default:
                return "unknown";
        }
    }

    use ToChoicesArrayTrait;
}