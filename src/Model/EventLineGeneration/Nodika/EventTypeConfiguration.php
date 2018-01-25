<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration\Nodika;

class EventTypeConfiguration
{
    /**
     * EventTypeConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (null !== $data) {
            $this->weekday = $data->weekday;
            $this->saturday = $data->saturday;
            $this->sunday = $data->sunday;
            $this->holiday = $data->holiday;
        } else {
            $this->weekday = 0;
            $this->saturday = 0;
            $this->sunday = 0;
            $this->holiday = 0;
        }
    }

    /* @var double $weekday */
    public $weekday;

    /* @var double $saturday */
    public $saturday;

    /* @var double $sunday */
    public $sunday;

    /* @var double $holiday */
    public $holiday;

    public function getSumOfDays()
    {
        return $this->weekday + $this->saturday + $this->sunday + $this->holiday;
    }
}
