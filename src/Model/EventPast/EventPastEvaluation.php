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
use App\Entity\Person;
use App\Enum\EventChangeType;

class EventPastEvaluation
{
    private $changedAtDateTime;
    private $changedByPerson;
    private $eventChangeType;
    private $hasChangedMember = false;
    private $oldMember;
    private $newMember;
    private $hasChangedPerson = false;
    private $oldPerson;
    private $newPerson;
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
     * @param Person $changedByPerson
     * @param int $eventChangeType
     */
    public function __construct(\DateTime $changedAtDateTime, Person $changedByPerson, $eventChangeType)
    {
        $this->changedAtDateTime = $changedAtDateTime;
        $this->changedByPerson = $changedByPerson;
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
     * call this function to show that the assigned Person has changed.
     *
     * @param Person $oldPerson
     * @param Person $newPerson
     */
    public function setPersonChanged(Person $oldPerson = null, Person $newPerson = null)
    {
        $this->hasChangedPerson = true;
        $this->oldPerson = $oldPerson;
        $this->newPerson = $newPerson;
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
     * @return Person
     */
    public function getChangedByPerson()
    {
        return $this->changedByPerson;
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
    public function hasChangedPerson()
    {
        return $this->hasChangedPerson;
    }

    /**
     * @return Person
     */
    public function getOldPerson()
    {
        return $this->oldPerson;
    }

    /**
     * @return Person
     */
    public function getNewPerson()
    {
        return $this->newPerson;
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
