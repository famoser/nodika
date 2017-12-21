<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:13
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