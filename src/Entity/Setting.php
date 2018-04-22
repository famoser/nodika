<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\ChangeAwareTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Setting extends BaseEntity
{
    use IdTrait;
    use ChangeAwareTrait;

    /**
     * the mail where enquiries submitted over the webpage are sent
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $supportMail;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $organisationName;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $memberName;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $frontendUserName;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $canConfirmDaysAdvance;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $mustConfirmDaysAdvance;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $send_remainder_days_interval;

    /**
     * @return string
     */
    public function getOrganisationName(): string
    {
        return $this->organisationName;
    }

    /**
     * @param string $organisationName
     */
    public function setOrganisationName(string $organisationName): void
    {
        $this->organisationName = $organisationName;
    }

    /**
     * @return string
     */
    public function getMemberName(): string
    {
        return $this->memberName;
    }

    /**
     * @param string $memberName
     */
    public function setMemberName(string $memberName): void
    {
        $this->memberName = $memberName;
    }

    /**
     * @return string
     */
    public function getFrontendUserName(): string
    {
        return $this->frontendUserName;
    }

    /**
     * @param string $frontendUserName
     */
    public function setFrontendUserName(string $frontendUserName): void
    {
        $this->frontendUserName = $frontendUserName;
    }

    /**
     * @return string
     */
    public function getSupportMail(): string
    {
        return $this->supportMail;
    }

    /**
     * @param string $supportMail
     */
    public function setSupportMail(string $supportMail): void
    {
        $this->supportMail = $supportMail;
    }

    /**
     * @return int
     */
    public function getCanConfirmDaysAdvance(): int
    {
        return $this->canConfirmDaysAdvance;
    }

    /**
     * @param int $canConfirmDaysAdvance
     */
    public function setCanConfirmDaysAdvance(int $canConfirmDaysAdvance): void
    {
        $this->canConfirmDaysAdvance = $canConfirmDaysAdvance;
    }

    /**
     * @return int
     */
    public function getMustConfirmDaysAdvance(): int
    {
        return $this->mustConfirmDaysAdvance;
    }

    /**
     * @param int $mustConfirmDaysAdvance
     */
    public function setMustConfirmDaysAdvance(int $mustConfirmDaysAdvance): void
    {
        $this->mustConfirmDaysAdvance = $mustConfirmDaysAdvance;
    }

    /**
     * @return int
     */
    public function getSendRemainderDaysInterval(): int
    {
        return $this->send_remainder_days_interval;
    }

    /**
     * @param int $send_remainder_days_interval
     */
    public function setSendRemainderDaysInterval(int $send_remainder_days_interval): void
    {
        $this->send_remainder_days_interval = $send_remainder_days_interval;
    }
}
