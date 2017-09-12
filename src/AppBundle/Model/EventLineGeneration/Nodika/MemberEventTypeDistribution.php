<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/09/2017
 * Time: 21:55
 */

namespace AppBundle\Model\EventLineGeneration\Nodika;


use AppBundle\Model\EventLineGeneration\Nodika\MemberConfiguration;

class MemberEventTypeDistribution
{
    /**
     * MemberEventTypeDistribution constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if ($data != null) {
            $this->memberConfiguration = new MemberConfiguration($data->memberConfiguration);
            $this->eventTypeConfiguration = new EventTypeConfiguration($data->eventTypeConfiguration);
        }
    }

    /* @var MemberConfiguration $memberConfiguration */
    public $memberConfiguration;
    /* @var EventTypeConfiguration $eventTypeConfiguration */
    public $eventTypeConfiguration;
}