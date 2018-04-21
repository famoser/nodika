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
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\InvitedTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Member is part of the organisation, and is responsible for the events assigned to it.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Member extends BaseEntity
{
    use IdTrait;
    use ThingTrait;
    use AddressTrait;
    use CommunicationTrait;
    use InvitedTrait;
    use SoftDeleteTrait;

    /**
     * @var FrontendUser[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\FrontendUser", mappedBy="members")
     * @ORM\OrderBy({"familyName" = "ASC", "givenName" = "ASC"})
     */
    private $frontendUsers;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="member")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->frontendUsers = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * Get persons.
     *
     * @return \Doctrine\Common\Collections\Collection|FrontendUser[]
     */
    public function getFrontendUsers()
    {
        return $this->frontendUsers;
    }

    /**
     * Get events.
     *
     * @return \Doctrine\Common\Collections\Collection|Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param FrontendUser $frontendUser
     */
    public function addFrontendUser(FrontendUser $frontendUser)
    {
        $this->getFrontendUsers()->add($frontendUser);
        $frontendUser->getMembers()->add($this);
    }

    /**
     * @param FrontendUser $frontendUser
     */
    public function removeFrontendUser(FrontendUser $frontendUser)
    {
        $this->getFrontendUsers()->removeElement($frontendUser);
        $frontendUser->getMembers()->removeElement($this);
    }

    public function __toString()
    {
        return $this->getName();
    }
}
