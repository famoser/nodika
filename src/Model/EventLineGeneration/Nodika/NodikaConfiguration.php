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

use App\Model\EventLineGeneration\Base\BaseConfiguration;

class NodikaConfiguration extends BaseConfiguration
{
    /**
     * NodikaConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->memberConfigurations = [];
        $this->memberEventTypeDistributions = [];
        $this->holidays = [];
        $this->beforeEvents = [];
        if (null !== $data) {
            foreach ($data->memberConfigurations as $key => $item) {
                $this->memberConfigurations[] = new MemberConfiguration($item);
            }
            foreach ($data->beforeEvents as $item) {
                $this->beforeEvents[] = $item;
            }
            foreach ($data->memberEventTypeDistributions as $key => $item) {
                $this->memberEventTypeDistributions[] = new MemberEventTypeDistribution($item);
            }
            foreach ($data->holidays as $holiday) {
                $this->holidays[] = new \DateTime($holiday->date);
            }
            $this->holidaysFilled = $data->holidaysFilled;
            $this->eventTypeConfiguration = new EventTypeConfiguration($data->eventTypeConfiguration);
        } else {
            $this->eventTypeConfiguration = new EventTypeConfiguration(null);
            $this->eventTypeConfiguration->weekday = 1.0;
            $this->eventTypeConfiguration->saturday = 1.2;
            $this->eventTypeConfiguration->sunday = 1.5;
            $this->eventTypeConfiguration->holiday = 2.0;
            $this->holidaysFilled = false;
            $this->memberEventTypeDistributionFilled = false;
        }
        parent::__construct($data);
    }

    /* @var MemberConfiguration[] $memberConfigurations */
    public $memberConfigurations;

    /* @var EventTypeConfiguration $eventTypeConfiguration */
    public $eventTypeConfiguration;

    /* @var MemberEventTypeDistribution[] $memberEventTypeDistributions */
    public $memberEventTypeDistributions;

    /* @var boolean $memberEventTypeDistributionFilled */
    public $memberEventTypeDistributionFilled;

    /* @var \DateTime[] $holidays */
    public $holidays;

    /* @var boolean $holidaysFilled */
    public $holidaysFilled;

    /* @var int[] $beforeEvents */
    public $beforeEvents;
}
