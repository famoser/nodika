<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:12
 */

namespace AppBundle\Model\EventLineGeneration\Base;


use AppBundle\Model\EventLineGeneration\RoundRobin\MemberConfiguration;

class BaseConfiguration
{
    public function __construct($data)
    {
        if ($data != null) {
            $this->startDateTime = new \DateTime($data->startDateTime->date);
            $this->endDateTime = new \DateTime($data->endDateTime->date);
            $this->lengthInHours = (int)$data->lengthInHours;
        } else {
            //default values
            $this->startDateTime = new \DateTime();
            $this->endDateTime = new \DateTime('now + 1 year');
            $this->lengthInHours = 12;
        }
    }

    /* @var \DateTime $startDateTime */
    public $startDateTime;

    /* @var \DateTime $startDateTime */
    public $endDateTime;

    /* @var int $lengthInHours */
    public $lengthInHours;
}