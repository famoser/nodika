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
    private $eventLine;
    /**
     * @var Member|null
     */
    private $member;
    /**
     * @var FrontendUser|null
     */
    private $frontendUser;
    /**
     * @var boolean|null
     */
    private $isConfirmed;
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
    public function getEventLine(): ?EventLine
    {
        return $this->eventLine;
    }

    /**
     * @param EventLine|null $eventLine
     */
    public function setEventLine(?EventLine $eventLine): void
    {
        $this->eventLine = $eventLine;
    }

    /**
     * @return Member|null
     */
    public function getMember(): ?Member
    {
        return $this->member;
    }

    /**
     * @param Member|null $member
     */
    public function setMember(?Member $member): void
    {
        $this->member = $member;
    }

    /**
     * @return FrontendUser|null
     */
    public function getFrontendUser(): ?FrontendUser
    {
        return $this->frontendUser;
    }

    /**
     * @param FrontendUser|null $frontendUser
     */
    public function setFrontendUser(?FrontendUser $frontendUser): void
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * @return bool|null
     */
    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    /**
     * @param bool|null $isConfirmed
     */
    public function setIsConfirmed(?bool $isConfirmed): void
    {
        $this->isConfirmed = $isConfirmed;
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
