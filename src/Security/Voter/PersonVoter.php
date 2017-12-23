<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14/05/2017
 * Time: 10:37
 */

namespace App\Security\Voter;

use App\Entity\FrontendUser;
use App\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PersonVoter extends MemberVoter
{
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
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::REMOVE, self::ADMINISTRATE))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Person) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Person $subject
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
        $own = $subject->getId() == $user->getPerson()->getId();
        if ($own) {
            return true;
        }

        $any = false;
        foreach ($subject->getMembers() as $member) {
            switch ($attribute) {
                case self::VIEW:
                    $any = $any || parent::voteOnAttribute(self::VIEW, $member, $token);
                    break;
                case self::EDIT:
                    $any = $any || parent::voteOnAttribute(self::ADMINISTRATE, $member, $token);
                    break;
                case self::ADMINISTRATE:
                case self::REMOVE:
                    $any = $any || parent::voteOnAttribute(self::ADMINISTRATE, $member, $token);
                    break;
            }
        }
        return $any;
    }
}
