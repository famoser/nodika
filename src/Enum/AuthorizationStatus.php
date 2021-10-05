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
    public const PENDING = 0;
    public const ACCEPTED = 1;
    public const DECLINED = 2;
    public const ACKNOWLEDGED = 3;
    public const WITHDRAWN = 4;
}
