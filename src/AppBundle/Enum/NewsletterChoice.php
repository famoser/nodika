<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 16:05
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;

class NewsletterChoice extends BaseEnum
{
    const REGISTER = 1;
    const REGISTER_INFO_ONLY = 2;
    const QUESTION = 3;
}