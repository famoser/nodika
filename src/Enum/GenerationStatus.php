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

class GenerationStatus extends BaseEnum
{
    const STARTED = 0;
    const SUCCESSFUL = 1;
    const NO_MATCHING_TARGET = 2;
    const NO_ALLOWED_TARGET_FOR_EVENT = 3;
    const TIMEOUT = 4;
    const PREDETERMINED_EVENT_CANT_BE_ASSIGNED = 5;
    const NO_TARGET_CAN_ASSUME_RESPONSIBILITY = 6;
    const UNKNOWN_ERROR = 10;
}
