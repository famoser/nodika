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

class NewsletterChoice extends BaseEnum
{
    const REGISTER = 1;
    const REGISTER_INFO_ONLY = 2;
    const QUESTION = 3;
}
