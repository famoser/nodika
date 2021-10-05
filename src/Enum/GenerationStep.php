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

class GenerationStep extends BaseEnum
{
    public const SET_START_END = 0;
    public const CHOOSE_TARGETS = 1;
    public const PREVIEW = 2;
}
