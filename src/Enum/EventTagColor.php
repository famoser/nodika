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

class EventTagColor extends BaseEnum
{
    public const RED = 1;
    public const YELLOW = 2;
    public const BLUE = 3;
    public const GREEN = 4;
}
