<?php

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:13
 */

namespace AppBundle\Model\EventLineGeneration\RoundRobin;


use AppBundle\Model\EventLineGeneration\Base\BaseOutput;

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