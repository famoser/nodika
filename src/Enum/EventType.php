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

class EventType extends BaseEnum
{
    const UNSPECIFIED = 0;
    const WEEKDAY = 1;
    const SATURDAY = 2;
    const SUNDAY = 3;
    const HOLIDAY = 4;
}
