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
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
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
    private $contactEmail;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * EmailService constructor.
     *
     * @param \Swift_Mailer     $mailer
     * @param ManagerRegistry $registry
     * @param LoggerInterface   $logger
     * @param Environment       $twig
     * @param string            $contactEmail
     */
    public function __construct(\Swift_Mailer $mailer, ManagerRegistry $registry, LoggerInterface $logger, Environment $twig, string $contactEmail)
    {
        $this->mailer = $mailer;
        $this->doctrine = $registry;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->contactEmail = $contactEmail;
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

        $message = (new \Swift_Message())
            ->setSubject($email->getSubject())
            ->setFrom($this->contactEmail)
            ->setTo($email->getReceiver());

        $body = $email->getBody();
        if (null !== $email->getActionLink()) {
            $body .= "\n\n".$email->getActionText().': '.$email->getActionLink();
        }
        $message->setBody($body, 'text/plain');

        if (EmailType::PLAIN_EMAIL !== $email->getEmailType()) {
            $message->addPart(
                $this->twig->render(
                    'email/view.html.twig',
                    ['email' => $email]
                ),
                'text/html'
            );
        }

        foreach ($email->getCarbonCopyArray() as $item) {
            $message->addCc($item);
        }
        $this->mailer->send($message);
    }

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
    public function sendTextEmail($receiver, $subject, $body, $carbonCopy = [])
    {
        $email = new Email();
        $email->setReceiver($receiver);
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setCarbonCopyArray($carbonCopy);
        $email->setEmailType(EmailType::TEXT_EMAIL);

        return $this->processEmail($email);
    }

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
    public function sendActionEmail($receiver, $subject, $body, $actionText, $actionLink, $carbonCopy = [])
    {
        $email = new Email();
        $email->setReceiver($receiver);
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setActionText($actionText);
        $email->setActionLink($actionLink);
        $email->setCarbonCopyArray($carbonCopy);
        $email->setEmailType(EmailType::ACTION_EMAIL);

        return $this->processEmail($email);
    }

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
    public function sendPlainEmail($receiver, $subject, $body, $carbonCopy = [])
    {
        $email = new Email();
        $email->setReceiver($receiver);
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setCarbonCopyArray($carbonCopy);
        $email->setEmailType(EmailType::PLAIN_EMAIL);

        return $this->processEmail($email);
    }
}
