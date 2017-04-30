<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 19:52
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;

class OfferStatus extends BaseEnum
{
    const OFFER_OPEN = 1;
    const OFFER_ACCEPTED = 2;
    const OFFER_DECLINED = 3;
    const OFFER_CLOSED = 4;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::OFFER_OPEN:
                return "open";
            case static::OFFER_ACCEPTED:
                return "accepted";
            case static::OFFER_DECLINED:
                return "declined";
            case static::OFFER_CLOSED:
                return "closed";
            default:
                return "unknown";
        }
    }
}