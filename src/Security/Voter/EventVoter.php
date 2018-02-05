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

use App\Entity\Event;
use App\Entity\EventLine;
use App\Entity\FrontendUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventVoter extends EventLineVoter
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
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::REMOVE], true)) {
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
        $user = $token->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        $eventLine = $subject->getEventLine();
        if (!$eventLine instanceof EventLine) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return parent::voteOnAttribute(self::VIEW, $eventLine, $token);
            case self::EDIT:
            case self::REMOVE:
                return parent::voteOnAttribute(self::ADMINISTRATE, $eventLine, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }
}
