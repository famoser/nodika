<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/12/2017
 * Time: 09:33
 */

namespace AppBundle\Service;


use AppBundle\Entity\Event;
use AppBundle\Entity\EventOffer;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Member;
use AppBundle\Entity\Newsletter;
use AppBundle\Entity\Person;
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Service\Interfaces\EmailServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EmailService implements EmailServiceInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $mailerEmail;

    /**
     * @var string
     */
    private $contactEmail;

    /**
     * EmailService constructor.
     * @param \Swift_Mailer $mailer
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param $mailerEmail
     * @param $contactEmail
     */
    public function __construct(\Swift_Mailer $mailer, RouterInterface $router, TranslatorInterface $translator, $mailerEmail, $contactEmail)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->translator = $translator;
        $this->mailerEmail = $mailerEmail;
        $this->contactEmail = $contactEmail;
    }


    public function sendRegisterConfirm(FrontendUser $user)
    {
        $registerLink = $this->router->generate(
            "access_register_confirm",
            ["confirmationToken" => $user->getResetHash()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans("register.subject", [], "email_access"))
            ->setFrom($this->mailerEmail)
            ->setTo($user->getEmail())
            ->setBody($this->translator->trans(
                "register.message",
                ["%register_link%" => $registerLink],
                "email_access"));
        $this->mailer->send($message);
    }

    public function sendConfirmLate(Event $unconfirmedEvent, $adminEmail = null)
    {
        $member = $unconfirmedEvent->getMember();

        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans("member_event_confirm_too_late_remainder.subject", [], "email_cronjob"))
            ->setFrom($this->mailerEmail);

        if ($unconfirmedEvent->getPerson() != null) {
            $message->setTo($unconfirmedEvent->getPerson()->getEmail());
            $message->addCc($member->getEmail());
            $owner = $unconfirmedEvent->getPerson()->getFullName();
        } else {
            $message->setTo($member->getEmail());
            $owner = $member->getName();
        }

        $message->setBody($this->translator->trans(
            "member_event_confirm_too_late_remainder.message",
            [
                "%link%" => $this->router->generate("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL),
                "%event_short%" =>
                    $unconfirmedEvent->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT) .
                    " - " .
                    $unconfirmedEvent->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT),
                "%owner%" => $owner
            ],
            "email_cronjob"));

        if ($adminEmail != null) {
            $message->addCc($adminEmail);
        }
        $this->mailer->send($message);
    }

    public function sendScheduledConfirmToMember(Member $member, $unconfirmedEventCount)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans("member_event_confirm_remainder.subject", [], "email_cronjob"))
            ->setFrom($this->mailerEmail)
            ->setTo($member->getEmail())
            ->setBody($this->translator->trans(
                "member_event_confirm_remainder.message",
                [
                    "%link%" => $this->router->generate("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL),
                    "%count%" => $unconfirmedEventCount
                ],
                "email_cronjob"));

        $this->mailer->send($message);
    }


    public function sendScheduledConfirmToPerson(Person $person, $unconfirmedEventCount)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans("member_event_confirm_remainder.subject", [], "email_cronjob"))
            ->setFrom($this->mailerEmail)
            ->setTo($person->getEmail())
            ->setBody($this->translator->trans(
                "member_event_confirm_remainder.message",
                [
                    "%link%" => $this->router->generate("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL),
                    "%count%" => $unconfirmedEventCount
                ],
                "email_cronjob"));
        $this->mailer->send($message);
    }

    public function sendEventOfferAccepted(EventOffer $eventOffer)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans("emails.offer_accepted.subject", [], "offer"))
            ->setFrom($this->mailerEmail)
            ->setTo($eventOffer->getOfferedByPerson()->getEmail())
            ->setBody($this->translator->trans(
                "emails.offer_accepted.message",
                ["%link%" => $this->router->generate("offer_review", ["eventOffer" => $eventOffer->getId()], UrlGeneratorInterface::ABSOLUTE_URL)],
                "offer"));
        $this->mailer->send($message);
    }

    public function sendEventOfferRejected(EventOffer $eventOffer)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans("emails.offer_rejected.subject", [], "offer"))
            ->setFrom($this->mailerEmail)
            ->setTo($eventOffer->getOfferedByPerson()->getEmail())
            ->setBody($this->translator->trans(
                "emails.offer_rejected.message",
                ["%link%" => $this->router->generate("offer_review", ["eventOffer" => $eventOffer->getId()], UrlGeneratorInterface::ABSOLUTE_URL)],
                "offer"));
        $this->mailer->send($message);
    }

    public function sendNewOfferReceived(EventOffer $eventOffer)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans("emails.new_offer.subject", [], "offer"))
            ->setFrom($this->mailerEmail)
            ->setTo($eventOffer->getOfferedToPerson()->getEmail())
            ->setBody($this->translator->trans(
                "emails.new_offer.message",
                ["%link%" => $this->router->generate("offer_review", ["eventOffer" => $eventOffer->getId()], UrlGeneratorInterface::ABSOLUTE_URL)],
                "offer"));
        $this->mailer->send($message);
    }

    public function sendContactMessage(Newsletter $newsletter)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject("Nachricht auf nodika")
            ->setFrom($this->mailerEmail)
            ->setTo($this->contactEmail)
            ->setBody("Sie haben eine Kontaktanfrage auf nodika erhalten: \n" .
                "\nListe: " . $newsletter->getChoice() .
                "\nEmail: " . $newsletter->getEmail() .
                "\nVorname: " . $newsletter->getGivenName() .
                "\nNachname: " . $newsletter->getFamilyName() .
                "\nNachricht: " . $newsletter->getMessage());
        $this->mailer->send($message);
    }
}