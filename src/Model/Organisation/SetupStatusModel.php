<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return bool
     */
    public function getHasMembers()
    {
        return $this->hasMembers;
    }

    /**
     * @param bool $hasMembers
     */
    public function setHasMembers($hasMembers)
    {
        $this->hasMembers = $hasMembers;
    }

    /**
     * @return bool
     */
    public function getHasEventLines()
    {
        return $this->hasEventLines;
    }

    /**
     * @param bool $hasEventLines
     */
    public function setHasEventLines($hasEventLines)
    {
        $this->hasEventLines = $hasEventLines;
    }

    /**
     * @return bool
     */
    public function getHasEvents()
    {
        return $this->hasEvents;
    }

    /**
     * @param bool $hasEvents
     */
    public function setHasEvents($hasEvents)
    {
        $this->hasEvents = $hasEvents;
    }

    /**
     * @return bool
     */
    public function getHasVisitedSettings()
    {
        return $this->hasVisitedSettings;
    }

    /**
     * @param bool $hasVisitedSettings
     */
    public function setHasVisitedSettings($hasVisitedSettings)
    {
        $this->hasVisitedSettings = $hasVisitedSettings;
    }

    /**
     * @return bool
     */
    public function getHasInvitedMembers()
    {
        return $this->hasInvitedMembers;
    }

    /**
     * @param bool $hasInvitedMembers
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
