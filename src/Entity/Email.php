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
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Email extends BaseEntity
{
    use IdTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $receiver;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $actionText;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $actionLink;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $carbonCopy;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $emailType = EmailType::TEXT_EMAIL;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $sentDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $visitedDateTime;

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getActionText()
    {
        return $this->actionText;
    }

    /**
     * @param string $actionText
     */
    public function setActionText(string $actionText)
    {
        $this->actionText = $actionText;
    }

    /**
     * @return string
     */
    public function getActionLink()
    {
        return $this->actionLink;
    }

    /**
     * @param string $actionLink
     */
    public function setActionLink(string $actionLink)
    {
        $this->actionLink = $actionLink;
    }

    /**
     * @return string
     */
    public function getCarbonCopy()
    {
        return $this->carbonCopy;
    }

    /**
     * @param string $carbonCopy
     */
    public function setCarbonCopy($carbonCopy)
    {
        $this->carbonCopy = $carbonCopy;
    }

    /**
     * @return \DateTime
     */
    public function getSentDateTime()
    {
        return $this->sentDateTime;
    }

    /**
     * @param \DateTime $sentDateTime
     */
    public function setSentDateTime(\DateTime $sentDateTime)
    {
        $this->sentDateTime = $sentDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getVisitedDateTime()
    {
        return $this->visitedDateTime;
    }

    /**
     * @param \DateTime $visitedDateTime
     */
    public function setVisitedDateTime(\DateTime $visitedDateTime)
    {
        $this->visitedDateTime = $visitedDateTime;
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getReceiver() . ' ' . $this->getSubject();
    }

    /**
     * @return mixed
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param mixed $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return int
     */
    public function getEmailType()
    {
        return $this->emailType;
    }

    /**
     * @param int $emailType
     */
    public function setEmailType(int $emailType)
    {
        $this->emailType = $emailType;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
    }
}
