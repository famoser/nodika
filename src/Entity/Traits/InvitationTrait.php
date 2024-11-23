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

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

trait InvitationTrait
{
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $invitationIdentifier = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastInvitation = null;

    /**
     * @throws \Exception
     */
    public function invite()
    {
        $this->invitationIdentifier = Uuid::uuid4();
        $this->lastInvitation = new \DateTime();

        return $this->invitationIdentifier;
    }

    public function invitationAccepted(): void
    {
        $this->invitationIdentifier = null;
    }

    /**
     * gets the invitation identifier.
     * will be null if invite is disabled, and will change when calling invite().
     *
     * @return string
     */
    public function getInvitationIdentifier()
    {
        return $this->invitationIdentifier;
    }

    public function getLastInvitation(): ?\DateTime
    {
        return $this->lastInvitation;
    }

    public function setLastInvitation(\DateTime $lastInvitation): void
    {
        $this->lastInvitation = $lastInvitation;
    }
}
