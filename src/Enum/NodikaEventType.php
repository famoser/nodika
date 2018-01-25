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

class NodikaEventType extends BaseEnum
{
    const WEEKDAY = 1;
    const SATURDAY = 2;
    const SUNDAYS = 3;
    const HOLIDAYS = 4;
}
