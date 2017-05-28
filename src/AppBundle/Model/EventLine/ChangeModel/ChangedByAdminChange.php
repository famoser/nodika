<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2017
 * Time: 16:06
 */

namespace AppBundle\Model\EventLine\ChangeModel;


use AppBundle\Model\EventLine\ChangeModel\Base\EventLineAdminChangeModel;

class ChangedByAdminChange extends EventLineAdminChangeModel
{
    public $changedMember;
    public $oldMemberName;
    public $newMemberName;

    public $changedStartDateTime = false;
    public $oldStartDateTime;
    public $newStartDateTime;

    public $changedEndDateTime = false;
    public $oldEndDateTime;
    public $newEndDateTime;
}