<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 09/12/2017
 * Time: 13:04
 */

namespace App\Enum;


use App\Enum\Base\BaseEnum;

class EmailType extends BaseEnum
{
    const TEXT_EMAIL = 1;
    const PLAIN_EMAIL = 2;
    const ACTION_EMAIL = 3;
}