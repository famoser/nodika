<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 17:25
 */

namespace AppBundle\Model\EventLineGeneration\RoundRobin;


use AppBundle\Entity\Member;

class MemberConfiguration
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
            $this->order = $data->order;
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
     * @param $order
     * @return static
     */
    public static function createFromMember(Member $member, $order)
    {
        $val = new static(null);
        $val->id = $member->getId();
        $val->name = $member->getName();
        $val->isEnabled = true;
        $val->order = $order;
        return $val;
    }

    /* @var int $id */
    public $id;
    /* @var string $name */
    public $name;
    /* @var bool $isEnabled */
    public $isEnabled;
    /* @var int $order */
    public $order;
}