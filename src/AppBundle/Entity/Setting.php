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
use Doctrine\ORM\Mapping as ORM;


/**
 * A Member is part of the organisation, and is responsible for the events assigned to it
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SettingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Setting extends BaseEntity
{
    use IdTrait;

    /**
     * @ORM\Column(type="text")
     */
    private $key;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var FrontendUser
     *
     * @ORM\ManyToOne(targetEntity="FrontendUser", inversedBy="settings")
     */
    private $frontendUser;

    /**
     * Set key
     *
     * @param string $key
     *
     * @return Setting
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Setting
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\FrontendUser $frontendUser
     *
     * @return Setting
     */
    public function setFrontendUser(\AppBundle\Entity\FrontendUser $frontendUser = null)
    {
        $this->frontendUser = $frontendUser;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\FrontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }

    /**
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getKey();
    }
}
