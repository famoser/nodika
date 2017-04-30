<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 12:59
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\ToChoicesArrayTrait;

class TradeTag extends BaseEnum
{
    const NO_TRADE = 1;
    const MAYBE_TRADE = 2;
    const WANT_TRADE = 3;
    const MUST_TRADE = 4;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::NO_TRADE:
                return "no trade";
            case static::MAYBE_TRADE:
                return "maybe trade";
            case static::WANT_TRADE:
                return "want trade";
            case static::MUST_TRADE:
                return "must trade";
            default:
                return "unknown";
        }
    }

    use ToChoicesArrayTrait;
}