<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Event;

use App\Entity\EventLine;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Entity\Person;

class SearchEventModel
{
    /**
     * @var Organisation
     */
    private $organisation;
    /**
     * @var \DateTime
     */
    private $startDateTime;
    /**
     * @var \DateTime
     */
    private $endDateTime = null;
    /**
     * @var EventLine
     */
    private $filterEventLine = null;
    /**
     * @var Member
     */
    private $filterMember = null;
    /**
     * @var Person
     */
    private $filterPerson = null;
    /**
     * @var int
     */
    private $maxResults = 3000;

    /**
     * SearchEventModel constructor.
     *
     * @param Organisation $organisation
     * @param \DateTime $startDateTime
     */
    public function __construct(Organisation $organisation, \DateTime $startDateTime)
    {
        $this->organisation = $organisation;
        $this->startDateTime = $startDateTime;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return \DateTime
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndDateTime()
    {
        return $this->endDateTime;
    }

    /**
     * @param \DateTime $endDateTime
     */
    public function setEndDateTime($endDateTime)
    {
        $this->endDateTime = $endDateTime;
    }

    /**
     * @return Member
     */
    public function getFilterMember()
    {
        return $this->filterMember;
    }

    /**
     * @param Member $filterMember
     */
    public function setFilterMember($filterMember)
    {
        $this->filterMember = $filterMember;
    }

    /**
     * @return Person
     */
    public function getFilterPerson()
    {
        return $this->filterPerson;
    }

    /**
     * @param Person $filterPerson
     */
    public function setFilterPerson($filterPerson)
    {
        $this->filterPerson = $filterPerson;
    }

    /**
     * @return int
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @param int $maxResults
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
    }

    /**
     * @return EventLine
     */
    public function getFilterEventLine()
    {
        return $this->filterEventLine;
    }

    /**
     * @param EventLine $filterEventLine
     */
    public function setFilterEventLine($filterEventLine)
    {
        $this->filterEventLine = $filterEventLine;
    }
}
