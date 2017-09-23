<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 16:49
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;

class PaymentStatus extends BaseEnum
{
    const NOT_PAYED = 1;
    const PAYED = 2;
}