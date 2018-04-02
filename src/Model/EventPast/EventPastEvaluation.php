<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventPast;

use App\Entity\Member;
use App\Entity\FrontendUser;
use App\Enum\EventChangeType;

class EventPastEvaluation
{
    private $changedAtDateTime;
    private $changedByFrontendUser;
    private $eventChangeType;
    private $hasChangedMember = false;
    private $oldMember;
    private $newMember;
    private $hasChangedFrontendUser = false;
    private $oldFrontendUser;
    private $newFrontendUser;
    private $hasChangedStartDateTime = false;
    private $oldStartDateTime;
    private $newStartDateTime;
    private $hasChangedEndDateTime = false;
    private $oldEndDateTime;
    private $newEndDateTime;

    /**
     * EventPastEvaluation constructor.
     *
     * @param \DateTime $changedAtDateTime
     * @param FrontendUser $changedByFrontendUser
     * @param int $eventChangeType
     */
    public function __construct(\DateTime $changedAtDateTime, FrontendUser $changedByFrontendUser, $eventChangeType)
    {
        $this->changedAtDateTime = $changedAtDateTime;
        $this->changedByFrontendUser = $changedByFrontendUser;
        $this->eventChangeType = $eventChangeType;
    }

    /**
     * call this function to show that the assigned Member has changed.
     *
     * @param Member $oldMember
     * @param Member $newMember
     */
    public function setMemberChanged(Member $oldMember = null, Member $newMember = null)
    {
        $this->hasChangedMember = true;
        $this->oldMember = $oldMember;
        $this->newMember = $newMember;
    }

    /**
     * call this function to show that the assigned FrontendUser has changed.
     *
     * @param FrontendUser $oldFrontendUser
     * @param FrontendUser $newFrontendUser
     */
    public function setFrontendUserChanged(FrontendUser $oldFrontendUser = null, FrontendUser $newFrontendUser = null)
    {
        $this->hasChangedFrontendUser = true;
        $this->oldFrontendUser = $oldFrontendUser;
        $this->newFrontendUser = $newFrontendUser;
    }

    /**
     * call this function to show that the startDateTime has changed.
     *
     * @param \DateTime $oldStartDateTime
     * @param \DateTime $newStartDateTime
     */
    public function setStartDateTimeChanged(\DateTime $oldStartDateTime = null, \DateTime $newStartDateTime)
    {
        $this->hasChangedStartDateTime = true;
        $this->oldStartDateTime = $oldStartDateTime;
        $this->newStartDateTime = $newStartDateTime;
    }

    /**
     * call this function to show that the endDateTime has changed.
     *
     * @param \DateTime $oldEndDateTime
     * @param \DateTime $newEndDateTime
     */
    public function setEndDateTimeChanged(\DateTime $oldEndDateTime = null, \DateTime $newEndDateTime)
    {
        $this->hasChangedEndDateTime = true;
        $this->oldEndDateTime = $oldEndDateTime;
        $this->newEndDateTime = $newEndDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getChangedAtDateTime()
    {
        return $this->changedAtDateTime;
    }

    /**
     * @return FrontendUser
     */
    public function getChangedByFrontendUser()
    {
        return $this->changedByFrontendUser;
    }

    /**
     * @return string
     */
    public function getEventChangeTypeText()
    {
        return EventChangeType::getTranslation($this->eventChangeType);
    }

    /**
     * @return bool
     */
    public function hasChangedMember()
    {
        return $this->hasChangedMember;
    }

    /**
     * @return Member
     */
    public function getOldMember()
    {
        return $this->oldMember;
    }

    /**
     * @return Member
     */
    public function getNewMember()
    {
        return $this->newMember;
    }

    /**
     * @return bool
     */
    public function hasChangedFrontendUser()
    {
        return $this->hasChangedFrontendUser;
    }

    /**
     * @return FrontendUser
     */
    public function getOldFrontendUser()
    {
        return $this->oldFrontendUser;
    }

    /**
     * @return FrontendUser
     */
    public function getNewFrontendUser()
    {
        return $this->newFrontendUser;
    }

    /**
     * @return bool
     */
    public function hasChangedStartDateTime()
    {
        return $this->hasChangedStartDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getOldStartDateTime()
    {
        return $this->oldStartDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getNewStartDateTime()
    {
        return $this->newStartDateTime;
    }

    /**
     * @return bool
     */
    public function hasChangedEndDateTime()
    {
        return $this->hasChangedEndDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getOldEndDateTime()
    {
        return $this->oldEndDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getNewEndDateTime()
    {
        return $this->newEndDateTime;
    }
}
