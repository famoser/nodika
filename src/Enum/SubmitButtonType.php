<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/09/2017
 * Time: 16:05
 */

namespace App\Enum;

use App\Enum\Base\BaseEnum;

class SubmitButtonType extends BaseEnum
{
    const SEND = 1;
    const CREATE = 2;
    const EDIT = 3;
    const REMOVE = 4;
    const LOGIN = 5;
    const REGISTER = 6;
    const SET_PASSWORD = 7;
    const RESET_PASSWORD = 8;
    const NOT_SET = 9;
    const NEXT = 10;
    const CONFIRM = 11;
    const APPLY = 12;
}
