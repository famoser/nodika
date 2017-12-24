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
    const NO_TRADE = 1;
    const MAYBE_TRADE = 2;
    const WANT_TRADE = 3;
    const MUST_TRADE = 4;
}
