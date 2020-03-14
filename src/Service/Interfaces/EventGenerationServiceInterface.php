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

use App\Entity\Doctor;
use App\Entity\EventGeneration;

interface EventGenerationServiceInterface
{
    /**
     * tries to generate the events
     * returns true if successful.
     */
    public function generate(EventGeneration $eventGeneration);

    public function persist(EventGeneration $eventGeneration, Doctor $creator);
}
