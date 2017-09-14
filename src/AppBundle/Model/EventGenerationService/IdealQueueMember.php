<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14/09/2017
 * Time: 08:39
 */

namespace AppBundle\Model\EventGenerationService;


class IdealQueueMember
{
    public $id;
    public $totalEventCount = 0;
    public $doneEventCount = 0;
    public $partDone = 0;

    public function setPartDone()
    {
        $this->partDone = $this->doneEventCount / $this->totalEventCount;
    }
}