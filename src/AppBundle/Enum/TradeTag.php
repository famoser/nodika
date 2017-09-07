<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 12:59
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;

class TradeTag extends BaseEnum
{
    const NO_TRADE = 1;
    const MAYBE_TRADE = 2;
    const WANT_TRADE = 3;
    const MUST_TRADE = 4;
}