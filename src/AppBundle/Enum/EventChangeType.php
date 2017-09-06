<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 13:05
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\EnumStaticPropertiesTrait;

class EventChangeType extends BaseEnum
{
    const CREATED_BY_ADMIN = 1;
    const CHANGED_BY_ADMIN = 2;
    const REMOVED_BY_ADMIN = 3;
    const PERSON_ASSIGNED = 4;
    const CHANGED_OWNER = 5;

    use EnumStaticPropertiesTrait;
}