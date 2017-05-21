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
    const CREATED_BY_ADMIN = 1;
    const CHANGED_BY_ADMIN = 2;
    const REMOVED_BY_ADMIN = 3;
    const PERSON_ASSIGNED = 4;
    const CHANGED_OWNER = 5;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::CREATED_BY_ADMIN:
                return "created_by_administration";
            case static::CHANGED_BY_ADMIN:
                return "changed_by_administration";
            case static::REMOVED_BY_ADMIN:
                return "removed_by_administration";
            case static::PERSON_ASSIGNED:
                return "person_assigned";
            case static::CHANGED_OWNER:
                return "owner_changed";
            default:
                return "unknown";
        }
    }

    use ToChoicesArrayTrait;
}