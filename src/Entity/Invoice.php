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
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\ThingTrait;
use App\Enum\InvoiceType;
use App\Enum\PaymentStatus;
use App\Helper\DateTimeFormatter;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Invoice contains information about what a user has bought and if he payed.
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
     * Get paymentDateTime.
     *
     * @return \DateTime
     */
    public function getPaymentDateTime()
    {
        return $this->paymentDateTime;
    }

    /**
     * Set paymentDateTime.
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
     * Get paymentStatus.
     *
     * @return int
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * Set paymentStatus.
     *
     * @param int $paymentStatus
     *
     * @return Invoice
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * Get invoiceType.
     *
     * @return int
     */
    public function getInvoiceType()
    {
        return $this->invoiceType;
    }

    /**
     * Set invoiceType.
     *
     * @param int $invoiceType
     *
     * @return Invoice
     */
    public function setInvoiceType($invoiceType)
    {
        $this->invoiceType = $invoiceType;

        return $this;
    }

    /**
     * Get invoiceDataJson.
     *
     * @return string
     */
    public function getInvoiceDataJson()
    {
        return $this->invoiceDataJson;
    }

    /**
     * Set invoiceDataJson.
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
     * Get organisation.
     *
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set organisation.
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
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getInvoiceDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT) . ' - ' . $this->getName();
    }

    /**
     * Get invoiceDateTime.
     *
     * @return \DateTime
     */
    public function getInvoiceDateTime()
    {
        return $this->invoiceDateTime;
    }

    /**
     * Set invoiceDateTime.
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
}
