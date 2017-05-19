<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:31
 */

namespace AppBundle\Security\Voter;


use AppBundle\Entity\Event;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Organisation;
use AppBundle\Repository\EventLineRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventVoter extends OrganisationVoter
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
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::REMOVE))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Event) {
            return false;
        }

        return true;

    }

    /**
     * @param string $attribute
     * @param Event $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($subject->getIsRemoved()) {
            return false;
        }

        $user = $token->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        $organisation = $subject->getEventLine()->getOrganisation();
        if (!$organisation instanceof Organisation) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
            case self::REMOVE:
                return parent::voteOnAttribute(self::ADMINISTRATE, $organisation, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }
}