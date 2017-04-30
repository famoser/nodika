<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 19:36
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\ToChoicesArrayTrait;
use function Couchbase\defaultDecoder;

class InvoiceType extends BaseEnum
{
    const REGISTRATION_FEE = 1;
    const YEARLY_FEE = 2;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::REGISTRATION_FEE:
                return "registration fee";
            case static::YEARLY_FEE:
                return "yearly fee";
            default:
                return "unknown";
        }
    }

    use ToChoicesArrayTrait;
}