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

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Doctor extends BaseEntity implements UserInterface, EquatableInterface
{
    use AddressTrait;
    use CommunicationTrait {
        UserTrait::getEmail insteadof CommunicationTrait;
        UserTrait::setEmail insteadof CommunicationTrait;
    }
    use IdTrait;
    use InvitationTrait;
    use PersonTrait;
    use SoftDeleteTrait;
    use UserTrait;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\Clinic>
     */
    #[ORM\JoinTable(name: 'doctor_clinics')]
    #[ORM\ManyToMany(targetEntity: \Clinic::class, inversedBy: 'doctors')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private \Doctrine\Common\Collections\Collection $clinics;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Event>
     */
    #[ORM\OneToMany(targetEntity: \Event::class, mappedBy: 'doctor')]
    #[ORM\OrderBy(['startDateTime' => 'ASC'])]
    private \Doctrine\Common\Collections\Collection $events;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $isAdministrator = false;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $receivesAdministratorMail = false;

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
    public function getClinics(): \Doctrine\Common\Collections\Collection
    {
        return $this->clinics;
    }

    public function getEvents(): \Doctrine\Common\Collections\Collection
    {
        return $this->events;
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return array (Role|string)[] The user roles
     */
    public function getRoles(): array
    {
        if ($this->isAdministrator()) {
            return ['ROLE_ADMIN'];
        }

        return ['ROLE_USER'];
    }

    /**
     * check if this is the same user.
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!($user instanceof static)) {
            return false;
        }

        return $this->isEqualToUser($user);
    }

    public function isAdministrator(): bool
    {
        return $this->isAdministrator;
    }

    public function setIsAdministrator(bool $isAdministrator): void
    {
        $this->isAdministrator = $isAdministrator;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \App\Entity\Clinic> $clinics
     */
    public function setClinics(\Doctrine\Common\Collections\Collection $clinics): void
    {
        $this->clinics = $clinics;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    public function isReceivesAdministratorMail(): bool
    {
        return $this->receivesAdministratorMail;
    }

    public function setReceivesAdministratorMail(bool $receivesAdministratorMail): void
    {
        $this->receivesAdministratorMail = $receivesAdministratorMail;
    }
}
