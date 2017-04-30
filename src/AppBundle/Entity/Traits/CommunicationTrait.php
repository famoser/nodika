<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:46
 */

namespace AppBundle\Entity\Traits;


use Doctrine\ORM\Mapping as ORM;
use ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator\MagicIssetTest;

trait CommunicationTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $webpage;

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     * @return static
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return static
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * get non-empty communication lines
     *
     * @return string[]
     */
    public function getCommunicationLines()
    {
        $res = [];
        if ($this->getPhone() != "")
            $res[] = $this->getPhone();
        if ($this->getEmail() != "")
            $res[] = $this->getEmail();
        return $res;
    }

    /**
     * get the communication identifier
     *
     * @return string
     */
    protected function getCommunicationIdentifier()
    {
        return implode(",", $this->getCommunicationLines());
    }
}