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
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Entity\Person;

class SearchEventModel
{
    /**
     * @var \DateTime
     */
    private $startDateTime;
    /**
     * @var \DateTime
     */
    private $endDateTime;
    /**
     * @var EventLine|null
     */
    private $filterEventLine;
    /**
     * @var Member|null
     */
    private $filterMember;
    /**
     * @var FrontendUser|null
     */
    private $filterFrontendUser;
    /**
     * @var int
     */
    private $maxResults = 3000;

    public function __construct($size = "month")
    {
        $this->startDateTime = new \DateTime();
        $this->endDateTime = new \DateTime("now + 1 " . $size);
    }

    /**
     * @return \DateTime
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * @param \DateTime $startDateTime
     */
    public function setStartDateTime(\DateTime $startDateTime): void
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndDateTime(): \DateTime
    {
        return $this->endDateTime;
    }

    /**
     * @param \DateTime $endDateTime
     */
    public function setEndDateTime(\DateTime $endDateTime): void
    {
        $this->endDateTime = $endDateTime;
    }

    /**
     * @return EventLine|null
     */
    public function getFilterEventLine(): ?EventLine
    {
        return $this->filterEventLine;
    }

    /**
     * @param EventLine|null $filterEventLine
     */
    public function setFilterEventLine(?EventLine $filterEventLine): void
    {
        $this->filterEventLine = $filterEventLine;
    }

    /**
     * @return Member|null
     */
    public function getFilterMember(): ?Member
    {
        return $this->filterMember;
    }

    /**
     * @param Member|null $filterMember
     */
    public function setFilterMember(?Member $filterMember): void
    {
        $this->filterMember = $filterMember;
    }

    /**
     * @return FrontendUser|null
     */
    public function getFilterFrontendUser(): ?FrontendUser
    {
        return $this->filterFrontendUser;
    }

    /**
     * @param FrontendUser|null $filterFrontendUser
     */
    public function setFilterFrontendUser(?FrontendUser $filterFrontendUser): void
    {
        $this->filterFrontendUser = $filterFrontendUser;
    }

    /**
     * @return int
     */
    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    /**
     * @param int $maxResults
     */
    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }
}
