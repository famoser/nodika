<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 21/05/2017
 * Time: 14:15
 */

namespace App\Filter;


use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DeleteFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        return $targetTableAlias . '.deleted_at IS NULL'; // getParameter applies quoting automatically
    }
}