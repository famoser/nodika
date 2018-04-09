<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 08/04/2018
 * Time: 20:09
 */

namespace App\EventGeneration;


use App\Entity\EventGenerationFrontendUser;
use App\Entity\EventGenerationMember;
use App\Entity\FrontendUser;
use App\Entity\Member;

class EventTarget
{
    /**
     * @var int
     */
    private $identifier;

    /**
     * @var EventGenerationFrontendUser
     */
    private $frontendUser;

    /**
     * @var EventGenerationMember
     */
    private $member;

    /**
     * EventTarget constructor.
     * @param $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param $identifier
     * @param EventGenerationFrontendUser $frontendUser
     * @return static
     */
    public static function fromFrontendUser($identifier, EventGenerationFrontendUser $frontendUser)
    {
        $new = new static($identifier);
        $new->frontendUser = $frontendUser;
        return $new;
    }

    /**
     * @param $identifier
     * @param EventGenerationMember $member
     * @return static
     */
    public static function fromMember($identifier, EventGenerationMember $member)
    {
        $new = new static($identifier);
        $new->member = $member;
        return $new;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->identifier;
    }
}