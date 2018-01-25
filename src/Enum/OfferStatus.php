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

class OfferStatus extends BaseEnum
{
    const CREATING = 0;
    const OPEN = 1;
    const ACCEPTED = 2;
    const REJECTED = 3;
    const CLOSED = 4;
}
