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
use AppBundle\Entity\EventLineGeneration;
use AppBundle\Entity\FrontendUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventLineGenerationVoter extends EventLineVoter
{
    /**
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // only vote on Post objects inside this voter
        if (!$subject instanceof EventLineGeneration) {
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
        $user = $token->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        $eventLine = $subject->getEventLine();
        if (!$eventLine instanceof EventLine) {
            return false;
        }

        return parent::voteOnAttribute(self::ADMINISTRATE, $eventLine, $token);
    }
}