<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:13
 */

namespace AppBundle\Model\EventLineGeneration\Nodika;


use AppBundle\Model\EventLineGeneration\Base\BaseOutput;

class NodikaOutput extends BaseOutput
{
    /* @var MemberConfiguration[] $memberConfiguration */
    public $memberConfiguration;
    /* @var int $nodikaStatusCode */
    public $nodikaStatusCode;
}