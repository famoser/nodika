<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 19:52
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
