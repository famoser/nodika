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
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $invitationIdentifier = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastInvitation;

    /**
     * @throws \Exception
     */
    public function invite()
    {
        $this->invitationIdentifier = Uuid::uuid4();
        $this->lastInvitation = new \DateTime();

        return $this->invitationIdentifier;
    }

    public function invitationAccepted()
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

    /**
     * @return \DateTime|null
     */
    public function getLastInvitation(): ?\DateTime
    {
        return $this->lastInvitation;
    }

    /**
     * @param \DateTime $lastInvitation
     */
    public function setLastInvitation(\DateTime $lastInvitation): void
    {
        $this->lastInvitation = $lastInvitation;
    }
}
