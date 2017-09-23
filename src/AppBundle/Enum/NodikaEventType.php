<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/09/2017
 * Time: 18:44
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;

class NodikaEventType extends BaseEnum
{
    const WEEKDAY = 1;
    const SATURDAY = 2;
    const SUNDAYS = 3;
    const HOLIDAYS = 4;
}