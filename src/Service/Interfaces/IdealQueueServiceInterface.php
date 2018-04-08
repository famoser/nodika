<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\Event;
use App\Entity\EventGeneration;

interface IdealQueueServiceInterface
{
    /**
     * return the next queue member
     */
    public function getNext();
}
