<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 19:52
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;

class OfferStatus extends BaseEnum
{
    const OFFER_CREATING = 0;
    const OFFER_OPEN = 1;
    const OFFER_ACCEPTED = 2;
    const OFFER_DECLINED = 3;
    const OFFER_CLOSED = 4;
}