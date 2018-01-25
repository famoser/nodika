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

class MemberEventTypeDistribution
{
    /**
     * MemberEventTypeDistribution constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (null !== $data) {
            $this->newMemberConfiguration = new MemberConfiguration($data->newMemberConfiguration);
            $this->eventTypeAssignment = new EventTypeConfiguration($data->eventTypeAssignment);
        }
    }

    /* @var MemberConfiguration $newMemberConfiguration */
    public $newMemberConfiguration;
    /* @var EventTypeConfiguration $eventTypeAssignment */
    public $eventTypeAssignment;
}
