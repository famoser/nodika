<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration\RoundRobin;

use App\Entity\Member;
use App\Model\EventLineGeneration\Base\BaseMemberConfiguration;

class MemberConfiguration extends BaseMemberConfiguration
{
    /**
     * MemberConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (null !== $data) {
            $this->order = $data->order;
        }
        parent::__construct($data);
    }

    /**
     * @param Member $member
     * @param $order
     *
     * @return static
     */
    public static function createFromMember(Member $member, $order)
    {
        $instance = new static(null);
        $instance->initializeFromMember($member);
        $instance->order = $order;

        return $instance;
    }

    /* @var int $order */
    public $order;
}
