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

class EventChangeType extends BaseEnum
{
    public const CREATED = 1;
    public const GENERATED = 2;
    public const CHANGED = 3;
    public const REMOVED = 4;
    public const TRADED_TO_NEW_OWNER = 7;
    public const DOCTOR_ASSIGNED = 8;
    public const CONFIRMED = 9;
}
