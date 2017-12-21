<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 22:21
 */

namespace App\Model\Organisation;


class SetupStatusModel
{
    private $hasMembers;
    private $hasEventLines;
    private $hasEvents;
    private $hasVisitedSettings;
    private $hasInvitedMembers;

    /**
     * @return boolean
     */
    public function getHasMembers()
    {
        return $this->hasMembers;
    }

    /**
     * @param boolean $hasMembers
     */
    public function setHasMembers($hasMembers)
    {
        $this->hasMembers = $hasMembers;
    }

    /**
     * @return boolean
     */
    public function getHasEventLines()
    {
        return $this->hasEventLines;
    }

    /**
     * @param boolean $hasEventLines
     */
    public function setHasEventLines($hasEventLines)
    {
        $this->hasEventLines = $hasEventLines;
    }

    /**
     * @return boolean
     */
    public function getHasEvents()
    {
        return $this->hasEvents;
    }

    /**
     * @param boolean $hasEvents
     */
    public function setHasEvents($hasEvents)
    {
        $this->hasEvents = $hasEvents;
    }

    /**
     * @return boolean
     */
    public function getHasVisitedSettings()
    {
        return $this->hasVisitedSettings;
    }

    /**
     * @param boolean $hasVisitedSettings
     */
    public function setHasVisitedSettings($hasVisitedSettings)
    {
        $this->hasVisitedSettings = $hasVisitedSettings;
    }

    /**
     * @return boolean
     */
    public function getHasInvitedMembers()
    {
        return $this->hasInvitedMembers;
    }

    /**
     * @param boolean $hasInvitedMembers
     */
    public function setHasInvitedMembers($hasInvitedMembers)
    {
        $this->hasInvitedMembers = $hasInvitedMembers;
    }

    /**
     * @return bool
     */
    public function getAllDone()
    {
        return $this->getHasMembers() && $this->getHasEventLines() && $this->getHasEvents() && $this->getHasVisitedSettings() && $this->getHasInvitedMembers();
    }
}