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

use App\Enum\InvitationStatus;
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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $invitationStatus = InvitationStatus::NOT_INVITED;

    /**
     * @throws \Exception
     */
    public function invite()
    {
        $this->invitationIdentifier = Uuid::uuid4();
        $this->invitationStatus = InvitationStatus::INVITED;

        return $this->invitationIdentifier;
    }

    public function invitationAccepted()
    {
        $this->invitationIdentifier = null;
        $this->invitationStatus = InvitationStatus::ACCEPTED;
    }

    /**
     * @return int
     */
    public function getInvitationStatus(): int
    {
        return $this->invitationStatus;
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
}
