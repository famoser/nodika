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
