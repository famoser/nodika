<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/09/2017
 * Time: 16:05
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\EnumStaticPropertiesTrait;

class SubmitButtonType extends BaseEnum
{
    const SEND = 1;
    const CREATE = 2;
    const EDIT = 3;
    const REMOVE = 4;

    use EnumStaticPropertiesTrait;
}