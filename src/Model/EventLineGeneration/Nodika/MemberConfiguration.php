<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration\Nodika;

use App\Entity\Member;
use App\Model\EventLineGeneration\Base\BaseMemberConfiguration;

class MemberConfiguration extends BaseMemberConfiguration
{
    /* @var double $points */
    public $points;
    /* @var double $endScore */
    public $endScore;
    /* @var double $order */
    public $luckyScore;

    /**
     * MemberConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (null !== $data) {
            $this->points = $data->points;
            $this->endScore = $data->endScore;
            $this->luckyScore = $data->luckyScore;
        }
        parent::__construct($data);
    }

    /**
     * @param Member $member
     *
     * @return static
     */
    public static function createFromMember(Member $member)
    {
        $instance = new static(null);
        $instance->initializeFromMember($member);
        $instance->points = 1;
        $instance->endScore = 0.0;
        $instance->luckyScore = 0.0;

        return $instance;
    }
}
