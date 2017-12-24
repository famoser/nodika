<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Event;

use App\Entity\EventLine;
use App\Model\Form\ImportFileModel;

class ImportEventModel extends ImportFileModel
{
    /**
     * @var EventLine
     */
    private $eventLine;

    /**
     * @return EventLine
     */
    public function getEventLine()
    {
        return $this->eventLine;
    }

    /**
     * @param EventLine $eventLine
     */
    public function setEventLine($eventLine)
    {
        $this->eventLine = $eventLine;
    }
}
