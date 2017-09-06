<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 16:49
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\EnumStaticPropertiesTrait;

class PaymentStatus extends BaseEnum
{
    const NOT_PAYED = 1;
    const PAYED = 2;

    use EnumStaticPropertiesTrait;
}