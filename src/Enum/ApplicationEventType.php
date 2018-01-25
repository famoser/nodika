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

class ApplicationEventType extends BaseEnum
{
    const CREATED_RECOMMENDED_EVENT_LINES = 1;
    const VISITED_SETTINGS = 2;
    const ACCOUNT_PART_OF_ORGANISATION = 3;
}
