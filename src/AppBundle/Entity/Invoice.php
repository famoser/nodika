<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\PersonTrait;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Enum\InvoiceType;
use AppBundle\Enum\PaymentStatus;
use AppBundle\Enum\TradeTag;
use Doctrine\ORM\Mapping as ORM;


/**
 * An Invoice contains information about what a user has bought and if he payed
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvoiceRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Invoice extends BaseEntity
{
    use IdTrait;
    use AddressTrait;
    use ThingTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    private $invoiceDateTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $paymentDateTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $paymentStatus = PaymentStatus::NOT_PAYED;

    /**
     * @ORM\Column(type="integer")
     */
    private $invoiceType = InvoiceType::REGISTRATION_FEE;

    /**
     * @ORM\Column(type="text")
     */
    private $invoiceDataJson;
    
    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="invoices")
     */
    private $organisation;

    /**
     * Set invoiceDateTime
     *
     * @param \DateTime $invoiceDateTime
     *
     * @return Invoice
     */
    public function setInvoiceDateTime($invoiceDateTime)
    {
        $this->invoiceDateTime = $invoiceDateTime;

        return $this;
    }

    /**
     * Get invoiceDateTime
     *
     * @return \DateTime
     */
    public function getInvoiceDateTime()
    {
        return $this->invoiceDateTime;
    }

    /**
     * Set paymentDateTime
     *
     * @param \DateTime $paymentDateTime
     *
     * @return Invoice
     */
    public function setPaymentDateTime($paymentDateTime)
    {
        $this->paymentDateTime = $paymentDateTime;

        return $this;
    }

    /**
     * Get paymentDateTime
     *
     * @return \DateTime
     */
    public function getPaymentDateTime()
    {
        return $this->paymentDateTime;
    }

    /**
     * Set paymentStatus
     *
     * @param integer $paymentStatus
     *
     * @return Invoice
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * Get paymentStatus
     *
     * @return integer
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * Set invoiceType
     *
     * @param integer $invoiceType
     *
     * @return Invoice
     */
    public function setInvoiceType($invoiceType)
    {
        $this->invoiceType = $invoiceType;

        return $this;
    }

    /**
     * Get invoiceType
     *
     * @return integer
     */
    public function getInvoiceType()
    {
        return $this->invoiceType;
    }

    /**
     * Set invoiceDataJson
     *
     * @param string $invoiceDataJson
     *
     * @return Invoice
     */
    public function setInvoiceDataJson($invoiceDataJson)
    {
        $this->invoiceDataJson = $invoiceDataJson;

        return $this;
    }

    /**
     * Get invoiceDataJson
     *
     * @return string
     */
    public function getInvoiceDataJson()
    {
        return $this->invoiceDataJson;
    }

    /**
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return Invoice
     */
    public function setOrganisation(\AppBundle\Entity\Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return \AppBundle\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }
}