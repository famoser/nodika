<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/09/2017
 * Time: 18:46
 */

namespace App\Model\EventLineGeneration\Nodika;


class EventTypeConfiguration
{
    /**
     * EventTypeConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if ($data != null) {
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