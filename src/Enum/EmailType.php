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

class EmailType extends BaseEnum
{
    public const TEXT_EMAIL = 1;
    public const PLAIN_EMAIL = 2;
    public const ACTION_EMAIL = 3;
}
