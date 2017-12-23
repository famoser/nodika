<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 11:31
 */

namespace App\Enum;

use App\Enum\Base\BaseEnum;

class MessageType extends BaseEnum
{
    const INFO = 0;
    const WARNING = 1;
    const ERROR = 2;
    const FATAL = 3;
}
