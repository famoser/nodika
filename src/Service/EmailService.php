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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class EmailService implements EmailServiceInterface
{
    /**
     * @var MailerInterface
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

    private LoggerInterface $logger;

    /**
     * EmailService constructor.
     * @param MailerInterface $mailer
     * @param ManagerRegistry $registry
     * @param Environment $twig
     * @param string $contactEmail
     * @param LoggerInterface $logger
     */
    public function __construct(MailerInterface $mailer, ManagerRegistry $registry, string $contactEmail, \Psr\Log\LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->doctrine = $registry;
        $this->contactEmail = $contactEmail;
        $this->logger = $logger;
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

        $this->sendEmail($email);
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

        $this->sendEmail($email);
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

        $this->sendEmail($email);
    }

    private function sendEmail(Email $email)
    {
        $email->setSentDateTime(new \DateTime());
        $email->setIdentifier(HashHelper::createNewResetHash());

        $message = (new TemplatedEmail())
            ->subject($email->getSubject())
            ->from($this->contactEmail)
            ->to($email->getReceiver());

        $body = $email->getBody();
        if (null !== $email->getActionLink()) {
            $body .= "\n\n".$email->getActionText().': '.$email->getActionLink();
        }
        $message = $message->context(['myemail' => $email])
            ->text($body);

        if (EmailType::PLAIN_EMAIL !== $email->getEmailType()) {
            $message = $message->htmlTemplate('email/view.html.twig');
        }

        foreach ($email->getCarbonCopyArray() as $item) {
            $message->addCc($item);
        }

        try {
            $this->mailer->send($message);

            $manager = $this->doctrine->getManager();
            $manager->persist($email);
            $manager->flush();
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('email send failed', ['exception' => $exception]);
        }
    }
}
