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

class MessageType extends BaseEnum
{
    public const INFO = 0;
    public const WARNING = 1;
    public const ERROR = 2;
    public const FATAL = 3;
}
