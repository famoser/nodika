<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 12:53
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\ToChoicesArrayTrait;

class DistributionType extends BaseEnum
{
    const ROUND_ROBIN = 1;
    const FAIR = 2;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::ROUND_ROBIN:
                return "round robin";
            case static::FAIR:
                return "fair";
            default:
                return "unknown";
        }
    }

    use ToChoicesArrayTrait;
}