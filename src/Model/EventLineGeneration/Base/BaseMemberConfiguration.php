<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration\Base;

use App\Entity\Member;

class BaseMemberConfiguration
{
    /* @var int $id */
    public $id;
    /* @var string $name */
    public $name;
    /* @var bool $isEnabled */
    public $isEnabled;

    /**
     * MemberConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (null !== $data) {
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
}
