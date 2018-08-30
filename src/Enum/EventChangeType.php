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
    const CREATED = 1;
    const GENERATED = 2;
    const CHANGED = 3;
    const REMOVED = 4;
    const TRADED_TO_NEW_OWNER = 7;
    const DOCTOR_ASSIGNED = 8;
    const CONFIRMED = 9;
}
