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

use App\Model\EventLineGeneration\Base\BaseOutput;

class NodikaOutput extends BaseOutput
{
    /* @var MemberConfiguration[] $memberConfiguration */
    public $memberConfiguration;
    /* @var EventTypeConfiguration $eventTypeConfiguration */
    public $eventTypeConfiguration;
    /* @var int $nodikaStatusCode */
    public $nodikaStatusCode;
    /* @var int[] $beforeEvents */
    public $beforeEvents;
}
