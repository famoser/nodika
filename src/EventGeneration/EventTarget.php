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
use App\Entity\Traits\EventGenerationTarget;

class EventTarget
{
    const NONE_IDENTIFIER = 0;
    private static $nextIdentifier = 1;

    /**
     * @var int
     */
    private $identifier;

    /**
     * @var EventGenerationFrontendUser|null
     */
    private $frontendUser;

    /**
     * @var EventGenerationMember|null
     */
    private $member;

    public function __construct()
    {
        $this->identifier = static::$nextIdentifier++;
    }

    /**
     * @param EventGenerationFrontendUser $frontendUser
     * @return static
     */
    public static function fromFrontendUser(EventGenerationFrontendUser $frontendUser)
    {
        $new = new static();
        $new->frontendUser = $frontendUser;
        return $new;
    }

    /**
     * @param EventGenerationMember $member
     * @return static
     */
    public static function fromMember(EventGenerationMember $member)
    {
        $new = new static();
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

    /**
     * @return EventGenerationTarget
     */
    public function getTarget()
    {
        if ($this->frontendUser == null) {
            return $this->member;
        }
        return $this->frontendUser;
    }

    /**
     * @return FrontendUser|null
     */
    public function getFrontendUser(): ?FrontendUser
    {
        if ($this->frontendUser != null) {
            return $this->frontendUser->getFrontendUser();
        }
        return null;
    }

    /**
     * @return Member|null
     */
    public function getMember(): ?Member
    {
        if ($this->member != null) {
            return $this->member->getMember();
        }
        return null;
    }
}
