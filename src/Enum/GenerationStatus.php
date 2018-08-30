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
    const NO_MATCHING_CLINIC = 2;
    const NO_ALLOWED_CLINIC_FOR_EVENT = 3;
    const TIMEOUT = 4;
    const UNKNOWN_ERROR = 10;
}
