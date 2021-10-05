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

class EventTagType extends BaseEnum
{
    public const CUSTOM = 0;
    public const BACKUP_SERVICE = 1;
    public const ACTIVE_SERVICE = 2;
}
