<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:06
 */

namespace AppBundle\Model\EventLineGeneration\Nodika;


use AppBundle\Model\EventLineGeneration\Base\BaseConfiguration;

class NodikaConfiguration extends BaseConfiguration
{
    /**
     * NodikaConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->memberConfigurations = [];
        $this->memberEventTypeDistributions = [];
        $this->holidays = [];
        $this->beforeEvents = [];
        if ($data != null) {
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

    /* @var boolean $holidaysFilled */
    public $holidaysFilled;

    /* @var boolean $memberEventTypeDistributionFilled */
    public $memberEventTypeDistributionFilled;

    /* @var \DateTime[] $holidays */
    public $holidays;

    /* @var int[] $beforeEvents */
    public $beforeEvents;
}