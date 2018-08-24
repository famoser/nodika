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
     * @param string   $receiver
     * @param string   $subject
     * @param string   $body
     * @param string[] $carbonCopy
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return bool
     */
    public function sendTextEmail($receiver, $subject, $body, $carbonCopy = []);

    /**
     * @param string   $receiver
     * @param string   $subject
     * @param string   $body
     * @param string[] $carbonCopy
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return bool
     */
    public function sendPlainEmail($receiver, $subject, $body, $carbonCopy = []);

    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param $actionText
     * @param string   $actionLink
     * @param string[] $carbonCopy
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return bool
     */
    public function sendActionEmail($receiver, $subject, $body, $actionText, $actionLink, $carbonCopy = []);
}
