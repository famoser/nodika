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

class InvitationStatus extends BaseEnum
{
    const NOT_INVITED = 0;
    const INVITED = 1;
    const ACCEPTED = 2;
}
