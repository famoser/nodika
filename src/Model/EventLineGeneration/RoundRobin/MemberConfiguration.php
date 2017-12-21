<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 17:25
 */

namespace App\Model\EventLineGeneration\RoundRobin;


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
            $this->order = $data->order;
        }
        parent::__construct($data);
    }

    /**
     * @param Member $member
     * @param $order
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