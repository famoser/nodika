<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Enum\EmailType;
use Doctrine\ORM\Mapping as ORM;


/**
 * An Email is a sent email to the specified receivers
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmailRepository")
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
     * @ORM\Column(type="datetime")
     */
    private $visitedDateTime;

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
    public function setCarbonCopy(string $carbonCopy)
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
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getReceiver() . " " . $this->getSubject();
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
}
