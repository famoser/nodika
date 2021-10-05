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
    public const UNSPECIFIED = 0;
    public const WEEKDAY = 1;
    public const SATURDAY = 2;
    public const SUNDAY = 3;
    public const HOLIDAY = 4;
}
