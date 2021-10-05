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

class TradeTag extends BaseEnum
{
    public const NO_TRADE = 1;
    public const MAYBE_TRADE = 2;
    public const WANT_TRADE = 3;
    public const MUST_TRADE = 4;
}
