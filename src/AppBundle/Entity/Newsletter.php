<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 15:51
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\PersonTrait;
use AppBundle\Enum\NewsletterChoice;

/**
 * A Member is part of the organisation, and is responsible for the events assigned to it
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsletterRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Newsletter
{
    use IdTrait;
    use PersonTrait;
    use CommunicationTrait;

    /**
     * @ORM\Column(type="integer")
     */
    private $choice = NewsletterChoice::REGISTER;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;
}