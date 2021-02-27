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
     */
    public function __construct(\Swift_Mailer $mailer, ManagerRegistry $registry, LoggerInterface $logger, Environment $twig, string $contactEmail)
    {
        $this->mailer = $mailer;
        $this->doctrine = $registry;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->contactEmail = $contactEmail;
    }

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
     */
    public function sendTextEmail($receiver, $subject, $body, $carbonCopy = [])
    {
        $email = new Email();
        $email->setReceiver($receiver);
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setCarbonCopyArray($carbonCopy);
        $email->setEmailType(EmailType::TEXT_EMAIL);

        $this->processEmail($email);
    }

    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param $actionText
     * @param string   $actionLink
     * @param string[] $carbonCopy
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

        $this->processEmail($email);
    }

    /**
     * @param string   $receiver
     * @param string   $subject
     * @param string   $body
     * @param string[] $carbonCopy
     */
    public function sendPlainEmail($receiver, $subject, $body, $carbonCopy = [])
    {
        $email = new Email();
        $email->setReceiver($receiver);
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setCarbonCopyArray($carbonCopy);
        $email->setEmailType(EmailType::PLAIN_EMAIL);

        $this->processEmail($email);
    }
}
