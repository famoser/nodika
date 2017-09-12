<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/09/2017
 * Time: 18:46
 */

namespace AppBundle\Model\EventLineGeneration\Nodika;


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
            $this->weekday = 1.0;
            $this->saturday = 1.5;
            $this->sunday = 1.5;
            $this->holiday = 2.0;
        }
    }

    /* @var double $weekday */
    public $weekday;

    /* @var double $saturday */
    public $saturday;

    /* @var double $sunday */
    public $sunday;

    /* @var double $holiday*/
    public $holiday;
}