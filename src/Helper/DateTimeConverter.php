<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

class DateTimeConverter
{
    /**
     * adds the specified amount of days to the dateTime.
     *
     * @param \DateTime $dateTime
     * @param $days
     *
     * @return \DateTime
     */
    public static function addDays(\DateTime $dateTime, $days)
    {
        $interval = new \DateInterval('P'.$days.'D');
        $dateTime->add($interval);

        return $dateTime;
    }
}
