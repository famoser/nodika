<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14/09/2017
 * Time: 16:17
 */

namespace AppBundle\Enum;


use AppBundle\Enum\Base\BaseEnum;
use function Couchbase\defaultDecoder;

class SettingKey extends BaseEnum
{
    const ACTIVE_MEMBER_ID = "active_member_id";

    /**
     * get the default content
     * @param $key
     * @return mixed
     */
    public static function getDefaultContent($key)
    {
        switch ($key) {
            case static::ACTIVE_MEMBER_ID:
                return -1;
            default:
                return null;
        }
    }
}