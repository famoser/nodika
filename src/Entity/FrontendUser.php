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
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FrontendUserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class FrontendUser extends BaseEntity implements AdvancedUserInterface, EquatableInterface
{
    use IdTrait;
    use PersonTrait;
    use AddressTrait;
    use CommunicationTrait;
    use UserTrait {
        CommunicationTrait::getEmail insteadof UserTrait::getEmail;
        CommunicationTrait::setEmail insteadof UserTrait::setEmail;
    }

    /**
     * @var Member[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Member", inversedBy="persons")
     * @ORM\JoinTable(name="person_members")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $members;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="person")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * Get members.
     *
     * @return \Doctrine\Common\Collections\Collection|Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Get events.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return array (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * check if this is the same user
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!($user instanceof static)) {
            return false;
        }

        return $this->isEqualToUser($user);
    }
}
