<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 16:49
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\ToChoicesArrayTrait;

class PaymentStatus extends BaseEnum
{
    const NOT_PAYED = 1;
    const PAYED = 2;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::NOT_PAYED:
                return "not payed";
            case static::PAYED;
                return "payed";
            default:
                return "unknown";
        }
    }

    use ToChoicesArrayTrait;
}