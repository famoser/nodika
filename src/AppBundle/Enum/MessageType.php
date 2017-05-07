<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 11:31
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\Base\ToChoicesArrayTrait;

class MessageType extends BaseEnum
{
    const INFO = 0;
    const WARNING = 1;
    const ERROR = 2;
    const FATAL = 3;

    /**
     * enum value to string
     *
     * @param $enumValue
     * @return string
     */
    public function toString($enumValue)
    {
        switch ($enumValue) {
            case static::INFO:
                return "info";
            case static::WARNING:
                return "warning";
            case static::ERROR:
                return "error";
            case static::FATAL:
                return "fatal";
            default:
                return "unknown";
        }
    }

    use ToChoicesArrayTrait;
}