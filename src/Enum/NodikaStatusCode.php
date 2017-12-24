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

class NodikaStatusCode extends BaseEnum
{
    const SUCCESSFUL = 1;
    const NO_MATCHING_MEMBER = 2;
    const NO_ALLOWED_MEMBER_FOR_EVENT = 3;
    const UNKNOWN_ERROR = 10;
}
