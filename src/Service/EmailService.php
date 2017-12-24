<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Email;
use App\Enum\EmailType;
use App\Helper\HashHelper;
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Twig\Environment;

class EmailService implements EmailServiceInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var string
     */
    private $mailerEmail;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * EmailService constructor.
     *
     * @param \Swift_Mailer     $mailer
     * @param RegistryInterface $registry
     * @param Environment       $twig
     * @param $mailerEmail
     */
    public function __construct(\Swift_Mailer $mailer, RegistryInterface $registry, Environment $twig, $mailerEmail)
    {
        $this->mailer = $mailer;
        $this->doctrine = $registry;
        $this->twig = $twig;
        $this->mailerEmail = $mailerEmail;
    }

    /**
     * @param Email $email
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function processEmail(Email $email)
    {
        $email->setSentDateTime(new \DateTime());
        $email->setIdentifier(HashHelper::createNewResetHash());

        $manager = $this->doctrine->getManager();
        $manager->persist($email);
        $manager->flush();

        $message = \Swift_Message::newInstance()
            ->setSubject($email->getSubject())
            ->setFrom($this->mailerEmail)
            ->setTo($email->getReceiver());

        $body = $email->getBody();
        if (null !== $email->getActionLink()) {
            $body .= "\n\n".$email->getActionText().': '.$email->getActionLink();
        }
        $message->setBody($body, 'text/plain');

        if (EmailType::PLAIN_EMAIL !== $email->getEmailType()) {
            $message->addPart(
                $this->twig->render(
                    'email/email.html.twig',
                    ['email' => $email]
                ),
                'text/html'
            );
        }

        if (null !== $email->getCarbonCopy()) {
            $message->addCc($email->getCarbonCopy());
        }
        $this->mailer->send($message);
    }

    /**
     * @param string      $receiver
     * @param string      $subject
     * @param string      $body
     * @param string|null $carbonCopy
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return bool
     */
    public function sendTextEmail($receiver, $subject, $body, $carbonCopy = null)
    {
        $email = new Email();
        $email->setReceiver($receiver);
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setCarbonCopy($carbonCopy);
        $email->setEmailType(EmailType::TEXT_EMAIL);

        return $this->processEmail($email);
    }

    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param $actionText
     * @param string      $actionLink
     * @param string|null $carbonCopy
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return bool
     */
    public function sendActionEmail($receiver, $subject, $body, $actionText, $actionLink, $carbonCopy = null)
    {
        $email = new Email();
        $email->setReceiver($receiver);
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setActionText($actionText);
        $email->setActionLink($actionLink);
        $email->setCarbonCopy($carbonCopy);
        $email->setEmailType(EmailType::ACTION_EMAIL);

        return $this->processEmail($email);
    }

    /**
     * @param string      $receiver
     * @param string      $subject
     * @param string      $body
     * @param string|null $carbonCopy
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return bool
     */
    public function sendPlainEmail($receiver, $subject, $body, $carbonCopy = null)
    {
        $email = new Email();
        $email->setReceiver($receiver);
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setCarbonCopy($carbonCopy);
        $email->setEmailType(EmailType::PLAIN_EMAIL);

        return $this->processEmail($email);
    }
}
