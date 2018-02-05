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
use Doctrine\ORM\Mapping as ORM;

/**
 * A Member is part of the organisation, and is responsible for the events assigned to it.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\SettingRepository")
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
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
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
     * Get user.
     *
     * @return FrontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }

    /**
     * Set user.
     *
     * @param FrontendUser $frontendUser
     *
     * @return Setting
     */
    public function setFrontendUser(FrontendUser $frontendUser = null)
    {
        $this->frontendUser = $frontendUser;

        return $this;
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key.
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
}
