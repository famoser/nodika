<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLine;

/*
 * Created by PhpStorm.
 * User: famoser
 * Date: 21/05/2017
 * Time: 15:50
 */

use App\Entity\Event;
use App\Entity\EventLine;

class EventLineModel
{
    /* @var EventLine $eventLine */
    public $eventLine;
    /* @var Event[] $events */
    public $events;
    /* @var Event[] $events */
    public $activeEvents;
}
