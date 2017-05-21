<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:31
 */

namespace AppBundle\Security\Voter;


use AppBundle\Entity\EventLine;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Organisation;
use AppBundle\Repository\EventLineRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventLineVoter extends OrganisationVoter
{
    /**
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::REMOVE, self::CREATE))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof EventLine) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param EventLine $subject
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

        $organisation = $subject->getOrganisation();
        if (!$organisation instanceof Organisation) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return parent::voteOnAttribute(self::VIEW, $organisation, $token);
            case self::EDIT:
            case self::CREATE:
            case self::REMOVE:
                return parent::voteOnAttribute(self::ADMINISTRATE, $organisation, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }
}