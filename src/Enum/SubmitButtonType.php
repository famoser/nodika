<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
