<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/2/18
 * Time: 10:10 AM
 */

namespace App\Entity\Traits;


use App\Entity\EventLine;
use App\Entity\EventGeneration;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Enum\EventType;
use App\Enum\TradeTag;

trait StartEndTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $startDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $endDateTime;

    /**
     * @return \DateTime
     */
    public function getStartDateTime(): ?\DateTime
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
    public function getEndDateTime(): ?\DateTime
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
}