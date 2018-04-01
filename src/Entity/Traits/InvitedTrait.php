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

use App\Helper\HashHelper;
use App\Helper\NamingHelper;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
     * will be null if invite is disabled, and will change when calling invite()
     *
     * @return string
     */
    public function getInvitationIdentifier()
    {
        return $this->invitationIdentifier;
    }

    /**
     * returns if the person can be invited
     *
     * @return bool
     */
    public function isInvited()
    {
        return $this->invitationIdentifier != null;
    }

    /**
     * clears the invitation identifier, effectively stopping the invitation
     */
    public function clearInvitation()
    {
        $this->invitationIdentifier = null;
    }

    /**
     *  creates a new identification identifier
     */
    public function invite()
    {
        $this->invitationIdentifier = Uuid::uuid4();
    }
}
