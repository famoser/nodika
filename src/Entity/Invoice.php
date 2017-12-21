<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\ThingTrait;
use App\Enum\InvoiceType;
use App\Enum\PaymentStatus;
use App\Helper\DateTimeFormatter;
use Doctrine\ORM\Mapping as ORM;


/**
 * An Invoice contains information about what a user has bought and if he payed
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
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
     * @param Organisation $organisation
     *
     * @return Invoice
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getInvoiceDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT) . " - " . $this->getName();
    }
}
