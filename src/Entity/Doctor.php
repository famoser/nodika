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
use App\Entity\Traits\InvitationTrait;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Doctor extends BaseEntity implements UserInterface, EquatableInterface
{
    use IdTrait;
    use UserTrait;
    use InvitationTrait;
    use PersonTrait;
    use AddressTrait;
    use CommunicationTrait {
        UserTrait::getEmail insteadof CommunicationTrait;
        UserTrait::setEmail insteadof CommunicationTrait;
    }
    use SoftDeleteTrait;

    /**
     * @var Clinic[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Clinic", inversedBy="doctors")
     * @ORM\JoinTable(name="doctor_clinics")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $clinics;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="doctor")
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isAdministrator = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $receivesAdministratorMail = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clinics = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**     *
     * @return \Doctrine\Common\Collections\Collection|Clinic[]
     */
    public function getClinics()
    {
        return $this->clinics;
    }

    /**
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
     * check if this is the same user.
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

    /**
     * @return bool
     */
    public function isAdministrator(): bool
    {
        return $this->isAdministrator;
    }

    /**
     * @param bool $isAdministrator
     */
    public function setIsAdministrator(bool $isAdministrator): void
    {
        $this->isAdministrator = $isAdministrator;
    }

    /**
     * @param Clinic[]|ArrayCollection $clinics
     */
    public function setClinics($clinics): void
    {
        $this->clinics = $clinics;
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * @return bool
     */
    public function isReceivesAdministratorMail(): bool
    {
        return $this->receivesAdministratorMail;
    }

    /**
     * @param bool $receivesAdministratorMail
     */
    public function setReceivesAdministratorMail(bool $receivesAdministratorMail): void
    {
        $this->receivesAdministratorMail = $receivesAdministratorMail;
    }
}
