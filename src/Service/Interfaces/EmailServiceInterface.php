<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/12/2017
 * Time: 09:33
 */

namespace App\Service\Interfaces;

interface EmailServiceInterface
{
    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param string|null $carbonCopy
     * @return boolean
     */
    public function sendTextEmail($receiver, $subject, $body, $carbonCopy = null);

    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param string|null $carbonCopy
     * @return boolean
     */
    public function sendPlainEmail($receiver, $subject, $body, $carbonCopy = null);

    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param $actionText
     * @param string $actionLink
     * @param string|null $carbonCopy
     * @return boolean
     */
    public function sendActionEmail($receiver, $subject, $body, $actionText, $actionLink, $carbonCopy = null);
}
