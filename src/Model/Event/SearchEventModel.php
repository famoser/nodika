<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/11/2017
 * Time: 10:15
 */

namespace App\Model\Event;

use App\Entity\Member;
use App\Entity\Organisation;
use App\Entity\Person;

class SearchEventModel
{
    /**
     * SearchEventModel constructor.
     * @param Organisation $organisation
     * @param \DateTime $startDateTime
     */
    public function __construct(Organisation $organisation, \DateTime $startDateTime)
    {
        $this->organisation = $organisation;
        $this->startDateTime = $startDateTime;
    }

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
}
