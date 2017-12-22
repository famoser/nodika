<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 19:36
 */

namespace App\Enum;


use App\Enum\Base\BaseEnum;

class InvoiceType extends BaseEnum
{
    const REGISTRATION_FEE = 1;
    const YEARLY_FEE = 2;
}