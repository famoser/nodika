<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24/09/2017
 * Time: 10:15
 */

namespace App\Model\Event;

use App\Model\Framework\DeserializedDateTime;

class DeserializedEvent
{
    /* @var int $id */
    public $id;
    /* @var DeserializedDateTime $startDateTime */
    public $startDateTime;
    /* @var DeserializedDateTime $endDateTime */
    public $endDateTime;
    /* @var int $eventLineId */
    public $eventLineId;
    /* @var int $memberId */
    public $memberId;
    /* @var int $personId */
    public $personId;
    /* @var int $tradeTag */
    public $tradeTag;
}
