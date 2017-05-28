<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2017
 * Time: 16:06
 */

namespace AppBundle\Model\EventLine\ChangeModel;


use AppBundle\Model\EventLine\ChangeModel\Base\EventLineAdminChangeModel;
use AppBundle\Model\EventLine\ChangeModel\Base\EventLineBaseChangeModel;

class CreatedByAdminChange extends EventLineAdminChangeModel
{
    public $adminIdentifier;
}