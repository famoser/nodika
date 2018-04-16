<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use App\Entity\FrontendUser;
use Doctrine\ORM\Mapping as ORM;

/*
 * automatically keeps track of creation time & last change time
 */

trait ChangeAwareTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastChangedAt;

    /**
     * @var FrontendUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\FrontendUser")
     */
    private $createdBy;

    /**
     * @var FrontendUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\FrontendUser")
     */
    private $lastChangedBy;


    /**
     * @ORM\PrePersist()
     */
    public function prePersistTime()
    {
        $this->createdAt = new \DateTime();
        $this->lastChangedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateTime()
    {
        $this->lastChangedAt = new \DateTime();
    }


    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastChangedAt()
    {
        return $this->lastChangedAt;
    }

    /**
     * @return FrontendUser
     */
    public function getCreatedBy(): FrontendUser
    {
        return $this->createdBy;
    }

    /**
     * @return FrontendUser
     */
    public function getLastChangedBy(): FrontendUser
    {
        return $this->lastChangedBy;
    }

    /**
     * register who has changed the entity
     *
     * @param FrontendUser $frontendUser
     */
    public function registerChangeBy(FrontendUser $frontendUser)
    {
        if ($this->createdBy == null) {
            $this->createdBy = $frontendUser;
        }
        $this->lastChangedBy = $frontendUser;
    }
}
