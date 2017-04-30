<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 13:05
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\ToChoicesArrayTrait;

class EventChangeType extends BaseEnum
{
    const CREATED = 1;
    const SWITCHED = 2;
    const MEMBER_ASSIGNED = 3;
    const PERSON_ASSIGNED = 4;
    const REMOVED = 5;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::CREATED:
                return "created";
            case static::SWITCHED:
                return "created";
            case static::MEMBER_ASSIGNED:
                return "member assigned";
            case static::PERSON_ASSIGNED:
                return "person assigned";
            case static::REMOVED:
                return "removed";
            default:
                return "unknown";
        }
    }

    use ToChoicesArrayTrait;
}