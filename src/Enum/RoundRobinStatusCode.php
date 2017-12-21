<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 20:51
 */

namespace App\Enum;


use App\Enum\Base\BaseEnum;

class RoundRobinStatusCode extends BaseEnum
{
    const SUCCESSFUL = 1;
    const NO_MATCHING_MEMBER = 2;
    const UNKNOWN_ERROR = 10;
}