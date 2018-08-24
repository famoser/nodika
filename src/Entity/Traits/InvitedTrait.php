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

trait InvitedTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $invitationIdentifier = null;

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
     * returns if the person can be invited.
     *
     * @return bool
     */
    public function isInvited()
    {
        return null !== $this->invitationIdentifier;
    }

    /**
     * clears the invitation identifier, effectively stopping the invitation.
     */
    public function clearInvitation()
    {
        $this->invitationIdentifier = null;
    }

    /**
     *  creates a new identification identifier.
     */
    public function invite()
    {
        $this->invitationIdentifier = Uuid::uuid4();
    }
}
