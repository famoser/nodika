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
use App\Entity\Traits\IdTrait;
use App\Enum\EmailType;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Email extends BaseEntity
{
    use IdTrait;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $receiver = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $identifier = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $subject = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $body = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $actionText = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $actionLink = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $carbonCopy = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $emailType = EmailType::TEXT_EMAIL;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $sentDateTime = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $visitedDateTime = null;

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getActionText(): ?string
    {
        return $this->actionText;
    }

    public function setActionText(string $actionText): void
    {
        $this->actionText = $actionText;
    }

    public function getActionLink(): ?string
    {
        return $this->actionLink;
    }

    public function setActionLink(string $actionLink): void
    {
        $this->actionLink = $actionLink;
    }

    public function getCarbonCopy(): ?string
    {
        return $this->carbonCopy;
    }

    public function setCarbonCopy(?string $carbonCopy): void
    {
        $this->carbonCopy = $carbonCopy;
    }

    /**
     * @return string[]
     */
    public function getCarbonCopyArray(): array
    {
        if (mb_strlen($this->carbonCopy) > 0) {
            return explode(',', $this->carbonCopy);
        }

        return [];
    }

    /**
     * @param string[] $carbonCopy
     */
    public function setCarbonCopyArray($carbonCopy): void
    {
        $this->carbonCopy = implode(',', $carbonCopy);
    }

    /**
     * @return \DateTime
     */
    public function getSentDateTime(): ?\DateTimeInterface
    {
        return $this->sentDateTime;
    }

    public function setSentDateTime(\DateTime $sentDateTime): void
    {
        $this->sentDateTime = $sentDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getVisitedDateTime(): ?\DateTimeInterface
    {
        return $this->visitedDateTime;
    }

    public function setVisitedDateTime(\DateTime $visitedDateTime): void
    {
        $this->visitedDateTime = $visitedDateTime;
    }

    /**
     * returns a string representation of this entity.
     */
    public function getFullIdentifier(): string
    {
        return $this->getReceiver().' '.$this->getSubject();
    }

    public function getReceiver(): ?string
    {
        return $this->receiver;
    }

    public function setReceiver(?string $receiver): void
    {
        $this->receiver = $receiver;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getEmailType(): ?int
    {
        return $this->emailType;
    }

    public function setEmailType(int $emailType): void
    {
        $this->emailType = $emailType;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}
