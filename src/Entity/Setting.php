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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $doctorsCanEditSelf = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $doctorsCanEditClinics = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $canConfirmDaysAdvance = 90;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $mustConfirmDaysAdvance = 10;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $sendRemainderDaysInterval = 7;

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
        return $this->sendRemainderDaysInterval;
    }

    /**
     * @param int $sendRemainderDaysInterval
     */
    public function setSendRemainderDaysInterval(int $sendRemainderDaysInterval): void
    {
        $this->sendRemainderDaysInterval = $sendRemainderDaysInterval;
    }
}
