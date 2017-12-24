<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter\Base;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class CrudVoter extends Voter
{
    const CREATE = 1;
    const VIEW = 2;
    const EDIT = 3;
    const REMOVE = 4;
}
