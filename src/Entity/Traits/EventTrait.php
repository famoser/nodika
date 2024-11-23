<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Enum\EventType;
use Doctrine\ORM\Mapping as ORM;

trait EventTrait
{
    use StartEndTrait;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $confirmDateTime = null;

    #[ORM\ManyToOne(targetEntity: \Doctor::class)]
    private ?Doctor $confirmedByDoctor = null;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastRemainderEmailSent = null;

    #[ORM\Column(type: 'integer')]
    private ?int $eventType = EventType::UNSPECIFIED;

    #[ORM\ManyToOne(targetEntity: \Clinic::class, inversedBy: 'events')]
    private ?Clinic $clinic = null;

    #[ORM\ManyToOne(targetEntity: \Doctor::class, inversedBy: 'events')]
    private ?Doctor $doctor = null;

    public function getEventType(): int
    {
        return $this->eventType;
    }

    public function setEventType(int $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getConfirmDateTime(): ?\DateTime
    {
        return $this->confirmDateTime;
    }

    public function getConfirmedByDoctor(): ?Doctor
    {
        return $this->confirmedByDoctor;
    }

    public function getLastRemainderEmailSent(): ?\DateTime
    {
        return $this->lastRemainderEmailSent;
    }

    public function setLastRemainderEmailSent(?\DateTime $lastRemainderEmailSent): void
    {
        $this->lastRemainderEmailSent = $lastRemainderEmailSent;
    }

    public function getClinic(): ?Clinic
    {
        return $this->clinic;
    }

    public function setClinic(?Clinic $clinic): void
    {
        $this->clinic = $clinic;
    }

    public function getDoctor(): ?Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(?Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    public function isConfirmed(): bool
    {
        return null !== $this->getConfirmDateTime();
    }

    public function confirm(Doctor $user): void
    {
        $this->confirmDateTime = new \DateTime();
        $this->confirmedByDoctor = $user;
    }

    public function undoConfirm(): void
    {
        $this->confirmedByDoctor = null;
        $this->confirmDateTime = null;
    }

    /**
     * @param EventTrait $eventTrait
     */
    protected function writeValues($eventTrait)
    {
        $this->setStartDateTime(clone $eventTrait->getStartDateTime());
        $this->setEndDateTime(clone $eventTrait->getEndDateTime());
        $this->confirmDateTime = $eventTrait->getConfirmDateTime();
        $this->confirmedByDoctor = $eventTrait->getConfirmedByDoctor();
        $this->lastRemainderEmailSent = $eventTrait->getLastRemainderEmailSent();
        $this->eventType = $this->getEventType();
        $this->clinic = $eventTrait->getClinic();
        $this->doctor = $eventTrait->getDoctor();
    }

    public function isActive(): bool
    {
        $now = new \DateTime();

        return $this->getStartDateTime() < $now && $this->getEndDateTime() > $now;
    }
}
