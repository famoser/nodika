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
    const MANUALLY_CREATED_BY_ADMIN = 1;
    const GENERATED_BY_ADMIN = 2;
    const MANUALLY_CHANGED_BY_ADMIN = 3;
    const MANUALLY_REMOVED_BY_ADMIN = 4;
    const PERSON_ASSIGNED_BY_ADMIN = 5;
    const MEMBER_ASSIGNED_BY_ADMIN = 6;
    const TRADED_TO_NEW_MEMBER = 7;
    const PERSON_ASSIGNED_BY_MEMBER = 8;
    const CONFIRMED_BY_MEMBER = 9;
    const CONFIRMED_BY_PERSON = 10;
}
