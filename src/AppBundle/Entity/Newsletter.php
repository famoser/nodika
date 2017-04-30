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
use Doctrine\ORM\Mapping as ORM;

/**
 * A Newsletter is a person subscribed (or not) to receive news about this application
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

    /**
     * Set choice
     *
     * @param integer $choice
     *
     * @return Newsletter
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * Get choice
     *
     * @return integer
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Newsletter
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set webpage
     *
     * @param string $webpage
     *
     * @return Newsletter
     */
    public function setWebpage($webpage)
    {
        $this->webpage = $webpage;

        return $this;
    }

    /**
     * Get webpage
     *
     * @return string
     */
    public function getWebpage()
    {
        return $this->webpage;
    }
}
