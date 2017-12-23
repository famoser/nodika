<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:12
 */

namespace App\Model\EventLineGeneration\Base;

class BaseConfiguration
{
    /**
     * BaseConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if ($data != null) {
            $this->startDateTime = new \DateTime($data->startDateTime->date);
            $this->endDateTime = new \DateTime($data->endDateTime->date);
            $this->lengthInHours = (int)$data->lengthInHours;
            $this->conflictPufferInHours = (int)$data->conflictPufferInHours;
            $this->eventLineConfiguration = [];
            foreach ($data->eventLineConfiguration as $item) {
                $this->eventLineConfiguration[] = new EventLineConfiguration($item);
            }
        } else {
            //default values
            $this->startDateTime = new \DateTime();
            $this->endDateTime = new \DateTime('now + 1 year');
            $this->lengthInHours = 24;
            $this->eventLineConfiguration = [];
            $this->conflictPufferInHours = 2;
        }
    }

    /* @var \DateTime $startDateTime */
    public $startDateTime;

    /* @var \DateTime $startDateTime */
    public $endDateTime;

    /* @var int $lengthInHours */
    public $lengthInHours;

    /* @var int $conflictPufferInHours */
    public $conflictPufferInHours;

    /* @var EventLineConfiguration[] $eventLineConfiguration */
    public $eventLineConfiguration;
}
