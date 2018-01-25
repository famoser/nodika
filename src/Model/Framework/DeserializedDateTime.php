<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Framework;

class DeserializedDateTime
{
    /* @var string $date */
    public $date;
    /* @var int $timezone_type */
    public $timezone_type;
    /* @var string $timezone */
    public $timezone;
}
