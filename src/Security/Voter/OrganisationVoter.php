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
use App\Entity\Organisation;
use App\Security\Voter\Base\CrudVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OrganisationVoter extends CrudVoter
{
    //same as edit
    const ADMINISTRATE = 3;

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::REMOVE], true)) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Organisation) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Organisation $subject
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

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::REMOVE:
                //deny delete in every case
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * checks if the person is a leader or a member of the organisation.
     *
     * @param Organisation $organisation
     * @param FrontendUser $user
     *
     * @return bool
     */
    private function canView(Organisation $organisation, FrontendUser $user)
    {
        // if they can edit, they can view
        if ($this->canEdit($organisation, $user)) {
            return true;
        }

        $members = $user->getPerson()->getMembers();
        foreach ($organisation->getMembers() as $organisationMember) {
            foreach ($members as $member) {
                if ($organisationMember->getId() === $member->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * checks if the person is part of the leader team.
     *
     * @param Organisation $organisation
     * @param FrontendUser $user
     *
     * @return bool
     */
    private function canEdit(Organisation $organisation, FrontendUser $user)
    {
        return $organisation->getLeaders()->contains($user->getPerson());
    }
}
