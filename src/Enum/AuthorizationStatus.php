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

class AuthorizationStatus extends BaseEnum
{
    const PENDING = 0;
    const ACCEPTED = 1;
    const DECLINED = 2;
    const ACKNOWLEDGED = 3;
    const WITHDRAWN = 4;
}
