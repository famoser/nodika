<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 17:25
 */

namespace App\Model\EventLineGeneration\Base;

use App\Entity\Member;

class BaseMemberConfiguration
{
    /**
     * MemberConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if ($data != null) {
            $this->id = $data->id;
            $this->name = $data->name;
            $this->isEnabled = $data->isEnabled;
        }
    }

    /**
     * @param Member $member
     */
    public function updateFromMember(Member $member)
    {
        $this->name = $member->getName();
    }

    /**
     * @param Member $member
     */
    protected function initializeFromMember(Member $member)
    {
        $this->id = $member->getId();
        $this->name = $member->getName();
        $this->isEnabled = true;
    }

    /* @var int $id */
    public $id;
    /* @var string $name */
    public $name;
    /* @var bool $isEnabled */
    public $isEnabled;
}
