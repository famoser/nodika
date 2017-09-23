<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 13:05
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;

class ApplicationEventType extends BaseEnum
{
    const CREATED_RECOMMENDED_EVENT_LINES = 1;
    const VISITED_SETTINGS = 2;
    const ACCOUNT_PART_OF_ORGANISATION = 3;
}