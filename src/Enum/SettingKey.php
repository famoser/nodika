<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum;

use App\Enum\Base\BaseEnum;

class SettingKey extends BaseEnum
{
    const ACTIVE_MEMBER_ID = 'active_member_id';

    /**
     * get the default content.
     *
     * @param $key
     *
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
