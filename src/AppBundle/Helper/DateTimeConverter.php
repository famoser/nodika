<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13/05/2017
 * Time: 13:22
 */

namespace AppBundle\Helper;


class DateTimeConverter
{
    /**
     * adds the specified amount of days to the dateTime
     *
     * @param \DateTime $dateTime
     * @param $days
     * @return \DateTime
     */
    public static function addDays(\DateTime $dateTime, $days)
    {
        $interval = new \DateInterval("P" . $days . "D");
        $dateTime->add($interval);
        return $dateTime;
    }
}