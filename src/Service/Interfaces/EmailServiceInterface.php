<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

interface EmailServiceInterface
{
    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param string|null $carbonCopy
     *
     * @return bool
     */
    public function sendTextEmail($receiver, $subject, $body, $carbonCopy = null);

    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param string|null $carbonCopy
     *
     * @return bool
     */
    public function sendPlainEmail($receiver, $subject, $body, $carbonCopy = null);

    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param $actionText
     * @param string $actionLink
     * @param string|null $carbonCopy
     *
     * @return bool
     */
    public function sendActionEmail($receiver, $subject, $body, $actionText, $actionLink, $carbonCopy = null);
}
