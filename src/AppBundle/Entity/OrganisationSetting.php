<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13/09/2017
 * Time: 21:37
 */

namespace AppBundle\Entity;


use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;


/**
 * OrganisationSetting saves the settings for an organisation
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrganisationSettingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrganisationSetting extends BaseEntity
{
    use IdTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $inviteEmailSubject = "";

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $inviteEmailMessage = "";

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $mustConfirmEventBeforeDays = 30;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $canConfirmEventBeforeDays = 60;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sendConfirmEventEmailDays = 14;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tradeEventDays = 45;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastConfirmEventEmailSend = null;

    /**
     * @var Organisation
     *
     * @ORM\OneToOne(targetEntity="Organisation")
     */
    private $organisation;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $receiverOfRemainders;

    /**
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getOrganisation()->getName() . " setting";
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * @return string
     */
    public function getInviteEmailSubject()
    {
        return $this->inviteEmailSubject;
    }

    /**
     * @param string $inviteEmailSubject
     */
    public function setInviteEmailSubject($inviteEmailSubject)
    {
        $this->inviteEmailSubject = $inviteEmailSubject;
    }

    /**
     * @return string
     */
    public function getInviteEmailMessage()
    {
        return $this->inviteEmailMessage;
    }

    /**
     * @param string $inviteEmailMessage
     */
    public function setInviteEmailMessage($inviteEmailMessage)
    {
        $this->inviteEmailMessage = $inviteEmailMessage;
    }

    /**
     * @return int
     */
    public function getMustConfirmEventBeforeDays()
    {
        return $this->mustConfirmEventBeforeDays;
    }

    /**
     * @param int $mustConfirmEventBeforeDays
     */
    public function setMustConfirmEventBeforeDays($mustConfirmEventBeforeDays)
    {
        $this->mustConfirmEventBeforeDays = $mustConfirmEventBeforeDays;
    }

    /**
     * @return int
     */
    public function getCanConfirmEventBeforeDays()
    {
        return $this->canConfirmEventBeforeDays;
    }

    /**
     * @param int $canConfirmEventBeforeDays
     */
    public function setCanConfirmEventBeforeDays($canConfirmEventBeforeDays)
    {
        $this->canConfirmEventBeforeDays = $canConfirmEventBeforeDays;
    }

    /**
     * @return int
     */
    public function getSendConfirmEventEmailDays()
    {
        return $this->sendConfirmEventEmailDays;
    }

    /**
     * @param int $sendConfirmEventEmailDays
     */
    public function setSendConfirmEventEmailDays($sendConfirmEventEmailDays)
    {
        $this->sendConfirmEventEmailDays = $sendConfirmEventEmailDays;
    }

    /**
     * @return \DateTime
     */
    public function getLastConfirmEventEmailSend()
    {
        return $this->lastConfirmEventEmailSend;
    }

    /**
     * @param \DateTime $lastConfirmEventEmailSend
     */
    public function setLastConfirmEventEmailSend($lastConfirmEventEmailSend)
    {
        $this->lastConfirmEventEmailSend = $lastConfirmEventEmailSend;
    }

    /**
     * @return int
     */
    public function getTradeEventDays()
    {
        return $this->tradeEventDays;
    }

    /**
     * @param int $tradeEventDays
     */
    public function setTradeEventDays($tradeEventDays)
    {
        $this->tradeEventDays = $tradeEventDays;
    }

    /**
     * @return Person
     */
    public function getReceiverOfRemainders()
    {
        return $this->receiverOfRemainders;
    }

    /**
     * @param Person $receiverOfRemainders
     */
    public function setReceiverOfRemainders($receiverOfRemainders)
    {
        $this->receiverOfRemainders = $receiverOfRemainders;
    }
}