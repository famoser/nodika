<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Exception;

class GenerationException extends \Exception
{
    public function __construct($generationStatus)
    {
        parent::__construct('', $generationStatus);
    }
}
