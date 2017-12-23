<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 17:25
 */

namespace App\Model\EventLineGeneration\Nodika;

use App\Entity\Member;
use App\Model\EventLineGeneration\Base\BaseMemberConfiguration;

class MemberConfiguration extends BaseMemberConfiguration
{
    /**
     * MemberConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if ($data != null) {
            $this->points = $data->points;
            $this->endScore = $data->endScore;
            $this->luckyScore = $data->luckyScore;
        }
        parent::__construct($data);
    }

    /**
     * @param Member $member
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

    /* @var double $points */
    public $points;
    /* @var double $endScore */
    public $endScore;
    /* @var double $order */
    public $luckyScore;
}
