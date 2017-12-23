<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:16
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
