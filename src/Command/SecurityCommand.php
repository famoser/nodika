<?php

namespace App\Command;

use SensioLabs\Security\Command\SecurityCheckerCommand;
use SensioLabs\Security\SecurityChecker;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/01/2018
 * Time: 19:47
 */
class SecurityCommand extends SecurityCheckerCommand
{
    public function __construct()
    {
        parent::__construct(new SecurityChecker());
    }
}
