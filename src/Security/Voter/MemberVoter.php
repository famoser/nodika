<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\FrontendUser;
use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MemberVoter extends OrganisationVoter
{
    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::REMOVE, self::ADMINISTRATE], true)) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Member) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute
     * @param Member         $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        //check if own member
        $own = $subject->getPersons()->contains($user->getPerson());

        $organisation = $subject->getOrganisation();

        switch ($attribute) {
            case self::VIEW:
                return $own || parent::voteOnAttribute(self::VIEW, $organisation, $token);
            case self::EDIT:
                return $own || parent::voteOnAttribute(self::ADMINISTRATE, $organisation, $token);
            case self::ADMINISTRATE:
            case self::REMOVE:
                return parent::voteOnAttribute(self::ADMINISTRATE, $organisation, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }
}
