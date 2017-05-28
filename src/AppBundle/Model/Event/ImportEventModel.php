<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2017
 * Time: 17:49
 */

namespace AppBundle\Model\Event;


use AppBundle\Entity\EventLine;
use AppBundle\Model\Form\ImportFileModel;

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