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
use App\Entity\Traits\ChangeAwareTrait;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\InvitedTrait;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Settings extends BaseEntity
{
    use IdTrait;
    use ChangeAwareTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $organisationName;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $memberName;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $frontendUserName;

    /**
     * @var string
     *
     * @ORM\Column(type="boolean")
     */
    private $useMembers = true;

    /**
     * @return string
     */
    public function getOrganisationName(): string
    {
        return $this->organisationName;
    }

    /**
     * @param string $organisationName
     */
    public function setOrganisationName(string $organisationName): void
    {
        $this->organisationName = $organisationName;
    }

    /**
     * @return string
     */
    public function getMemberName(): string
    {
        return $this->memberName;
    }

    /**
     * @param string $memberName
     */
    public function setMemberName(string $memberName): void
    {
        $this->memberName = $memberName;
    }

    /**
     * @return string
     */
    public function getFrontendUserName(): string
    {
        return $this->frontendUserName;
    }

    /**
     * @param string $frontendUserName
     */
    public function setFrontendUserName(string $frontendUserName): void
    {
        $this->frontendUserName = $frontendUserName;
    }

    /**
     * @return string
     */
    public function getUseMembers(): string
    {
        return $this->useMembers;
    }

    /**
     * @param string $useMembers
     */
    public function setUseMembers(string $useMembers): void
    {
        $this->useMembers = $useMembers;
    }
}
