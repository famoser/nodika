<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration\Base;

class BaseConfiguration
{
    /**
     * BaseConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (null !== $data) {
            $this->startDateTime = new \DateTime($data->startDateTime->date);
            $this->endDateTime = new \DateTime($data->endDateTime->date);
            $this->lengthInHours = (int) $data->lengthInHours;
            $this->conflictPufferInHours = (int) $data->conflictPufferInHours;
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
