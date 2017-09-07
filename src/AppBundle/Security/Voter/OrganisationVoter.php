<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 09:37
 */

namespace AppBundle\Security\Voter;


use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Security\Voter\Base\CrudVoter;
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
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::REMOVE))) {
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
     * checks if the person is a leader or a member of the organisation
     *
     * @param Organisation $organisation
     * @param FrontendUser $user
     * @return bool
     */
    private function canView(Organisation $organisation, FrontendUser $user)
    {
        // if they can edit, they can view
        if ($this->canEdit($organisation, $user)) {
            return true;
        }

        $members = $user->getPerson()->getMembers();
        return $organisation->getMembers()->forAll(function ($key, $member) use ($members) {
            /* @var Member $member */
            return $members->contains($member);
        });
    }

    /**
     * checks if the person is part of the leader team
     *
     * @param Organisation $organisation
     * @param FrontendUser $user
     * @return bool
     */
    private function canEdit(Organisation $organisation, FrontendUser $user)
    {
        return $organisation->getLeaders()->contains($user->getPerson());
    }
}