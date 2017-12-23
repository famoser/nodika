<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 12:53
 */

namespace App\Enum;

use App\Enum\Base\BaseEnum;

class DistributionType extends BaseEnum
{
    const ROUND_ROBIN = 1;
    const NODIKA = 2;
}
