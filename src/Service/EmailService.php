<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/12/2017
 * Time: 09:33
 */

namespace App\Service;


use App\Entity\Email;
use App\Entity\Event;
use App\Entity\EventOffer;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Person;
use App\Entity\Traits\UserTrait;
use App\Enum\EmailType;
use App\Helper\DateTimeFormatter;
use App\Helper\HashHelper;
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
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
     * @param \Swift_Mailer $mailer
     * @param RegistryInterface $registry
     * @param Environment $twig
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
        if ($email->getActionLink() != null) {
            $body .= "\n\n" . $email->getActionText() . ": " . $email->getActionLink();
        }
        $message->setBody($body, 'text/plain');

        if ($email->getEmailType() != EmailType::PLAIN_EMAIL) {
            $message->addPart(
                $this->twig->render(
                    "email/email.html.twig", ["email" => $email]
                ),
                'text/html');
        }

        if ($email->getCarbonCopy() != null) {
            $message->addCc($email->getCarbonCopy());
        }
        $this->mailer->send($message);
    }

    /**
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param string|null $carbonCopy
     * @return boolean
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
     * @param string $actionLink
     * @param string|null $carbonCopy
     * @return boolean
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
     * @param string $receiver
     * @param string $subject
     * @param string $body
     * @param string|null $carbonCopy
     * @return boolean
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