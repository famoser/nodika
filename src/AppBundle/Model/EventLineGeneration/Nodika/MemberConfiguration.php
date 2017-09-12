<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 17:25
 */

namespace AppBundle\Model\EventLineGeneration\Nodika;


use AppBundle\Entity\Member;
use AppBundle\Model\EventLineGeneration\Base\BaseMemberConfiguration;

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
        $instance->luckyScore = 0;
        return $instance;
    }

    /* @var int $order */
    public $points;
    /* @var int $order */
    public $luckyScore;
}