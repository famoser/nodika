<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 08/04/2018
 * Time: 10:42
 */

namespace App\Exception;

class GenerationException extends \Exception
{
    public function __construct($generationStatus)
    {
        parent::__construct("", $generationStatus);
    }
}