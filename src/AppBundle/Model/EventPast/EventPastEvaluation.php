<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/09/2017
 * Time: 10:49
 */

namespace AppBundle\Model\EventPast;


use AppBundle\Entity\Member;
use AppBundle\Entity\Person;
use AppBundle\Enum\EventChangeType;

class EventPastEvaluation
{
    /**
     * EventPastEvaluation constructor.
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

    private $changedAtDateTime;
    private $changedByPerson;
    private $eventChangeType;

    /**
     * call this function to show that the assigned Member has changed
     *
     * @param Member $oldMember
     * @param Member $newMember
     */
    public function setMemberChanged(Member $oldMember = null, Member $newMember)
    {
        $this->hasChangedMember = true;
        $this->oldMember = $oldMember;
        $this->newMember = $newMember;
    }

    private $hasChangedMember = false;
    private $oldMember;
    private $newMember;

    /**
     * call this function to show that the assigned Person has changed
     * @param Person $oldPerson
     * @param Person $newPerson
     */
    public function setPersonChanged(Person $oldPerson = null, Person $newPerson)
    {
        $this->hasChangedPerson = true;
        $this->oldPerson = $oldPerson;
        $this->newPerson = $newPerson;
    }

    private $hasChangedPerson = false;
    private $oldPerson;
    private $newPerson;

    /**
     * call this function to show that the startDateTime has changed
     * @param \DateTime $oldStartDateTime
     * @param \DateTime $newStartDateTime
     */
    public function setStartDateTimeChanged(\DateTime $oldStartDateTime = null, \DateTime $newStartDateTime)
    {
        $this->hasChangedStartDateTime = true;
        $this->oldStartDateTime = $oldStartDateTime;
        $this->newStartDateTime = $newStartDateTime;
    }

    private $hasChangedStartDateTime = false;
    private $oldStartDateTime;
    private $newStartDateTime;

    /**
     * call this function to show that the endDateTime has changed
     * @param \DateTime $oldEndDateTime
     * @param \DateTime $newEndDateTime
     */
    public function setEndDateTimeChanged(\DateTime $oldEndDateTime = null, \DateTime $newEndDateTime)
    {
        $this->hasChangedEndDateTime = true;
        $this->oldEndDateTime = $oldEndDateTime;
        $this->newEndDateTime = $newEndDateTime;
    }

    private $hasChangedEndDateTime = false;
    private $oldEndDateTime;
    private $newEndDateTime;

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