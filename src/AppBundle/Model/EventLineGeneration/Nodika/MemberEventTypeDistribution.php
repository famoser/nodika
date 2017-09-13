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
            $this->newMemberConfiguration = new MemberConfiguration($data->newMemberConfiguration);
            $this->eventTypeAssignment = new EventTypeConfiguration($data->eventTypeAssignment);
        }
    }

    /* @var MemberConfiguration $newMemberConfiguration */
    public $newMemberConfiguration;
    /* @var EventTypeConfiguration $eventTypeAssignment */
    public $eventTypeAssignment;
}