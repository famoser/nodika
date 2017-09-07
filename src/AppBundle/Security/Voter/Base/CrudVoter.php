<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 09:38
 */

namespace AppBundle\Security\Voter\Base;


use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class CrudVoter extends Voter
{
    const CREATE = 1;
    const VIEW = 2;
    const EDIT = 3;
    const REMOVE = 4;
}