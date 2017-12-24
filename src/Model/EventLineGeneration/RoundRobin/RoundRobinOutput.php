<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration\RoundRobin;

use App\Model\EventLineGeneration\Base\BaseOutput;

class RoundRobinOutput extends BaseOutput
{
    /* @var MemberConfiguration[] $memberConfiguration */
    public $memberConfiguration;
    /* @var MemberConfiguration[] $memberConfiguration */
    public $priorityQueue;
    /* @var int $activeIndex */
    public $activeIndex;
    /* @var int $roundRobinStatusCode */
    public $roundRobinStatusCode;
}
