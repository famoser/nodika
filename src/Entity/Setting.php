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

#[ORM\Entity(repositoryClass: \App\Repository\SettingsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Setting extends BaseEntity
{
    use ChangeAwareTrait;
    use IdTrait;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $doctorsCanEditSelf = true;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $doctorsCanEditClinics = false;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $canConfirmDaysAdvance = 90;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $mustConfirmDaysAdvance = 10;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $sendRemainderDaysInterval = 7;

    public function getCanConfirmDaysAdvance(): int
    {
        return $this->canConfirmDaysAdvance;
    }

    public function setCanConfirmDaysAdvance(int $canConfirmDaysAdvance): void
    {
        $this->canConfirmDaysAdvance = $canConfirmDaysAdvance;
    }

    public function getMustConfirmDaysAdvance(): int
    {
        return $this->mustConfirmDaysAdvance;
    }

    public function setMustConfirmDaysAdvance(int $mustConfirmDaysAdvance): void
    {
        $this->mustConfirmDaysAdvance = $mustConfirmDaysAdvance;
    }

    public function getSendRemainderDaysInterval(): int
    {
        return $this->sendRemainderDaysInterval;
    }

    public function setSendRemainderDaysInterval(int $sendRemainderDaysInterval): void
    {
        $this->sendRemainderDaysInterval = $sendRemainderDaysInterval;
    }

    public function getDoctorsCanEditSelf(): bool
    {
        return $this->doctorsCanEditSelf;
    }

    public function setDoctorsCanEditSelf(bool $doctorsCanEditSelf): void
    {
        $this->doctorsCanEditSelf = $doctorsCanEditSelf;
    }

    public function getDoctorsCanEditClinics(): bool
    {
        return $this->doctorsCanEditClinics;
    }

    public function setDoctorsCanEditClinics(bool $doctorsCanEditClinics): void
    {
        $this->doctorsCanEditClinics = $doctorsCanEditClinics;
    }
}
