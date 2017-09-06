<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 19:36
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\EnumStaticPropertiesTrait;
use function Couchbase\defaultDecoder;

class InvoiceType extends BaseEnum
{
    const REGISTRATION_FEE = 1;
    const YEARLY_FEE = 2;

    use EnumStaticPropertiesTrait;
}