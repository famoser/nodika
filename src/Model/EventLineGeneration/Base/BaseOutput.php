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

use App\Model\EventLineGeneration\GenerationResult;

class BaseOutput
{
    /* @var \DateTime $startDateTime */
    public $endDateTime;

    /* @var int $lengthInHours */
    public $lengthInHours;

    /* @var int $version : version of the algorithm */
    public $version;

    /* @var GenerationResult $generationResult */
    public $generationResult;
}
