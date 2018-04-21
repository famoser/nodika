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
    const CREATED_BY_ADMIN = 1;
    const GENERATED_BY_ADMIN = 2;
    const CHANGED_BY_ADMIN = 3;
    const REMOVED_BY_ADMIN = 4;
    const TRADED_TO_NEW_MEMBER = 7;
    const PERSON_ASSIGNED_BY_MEMBER = 8;
    const CONFIRMED_BY_MEMBER = 9;
    const CONFIRMED_BY_PERSON = 10;
}
