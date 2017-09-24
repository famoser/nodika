<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\Member;
use AppBundle\Entity\Person;
use AppBundle\Helper\DateTimeFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/cron")
 * @Security("has_role('ROLE_USER')")
 */
class CronJobController extends BaseFrontendController
{
    /**
     * @Route("/test/{secret}", name="cron_test")
     * @param Request $request
     * @param $secret
     * @return Response
     */
    public function testAction(Request $request, $secret)
    {
        return new Response($secret == $this->getParameter("secret") ? "successful" : "access denied");
    }

    /**
     * @Route("/hourly/{secret}", name="cron_hourly")
     * @param Request $request
     * @param $secret
     * @return Response
     */
    public function hourlyAction(Request $request, $secret)
    {
        return new Response($secret == $this->getParameter("secret") ? "successful" : "access denied");
    }

    /**
     * @Route("/daily/{secret}", name="cron_daily")
     * @param Request $request
     * @param $secret
     * @return Response
     */
    public function dailyAction(Request $request, $secret)
    {
        if ($secret != $this->getParameter("secret")) {
            return new Response("access denied");
        }

        $trans = $this->get("translator");
        $mailer = $this->get("mailer");
        $memberRepo = $this->getDoctrine()->getRepository("AppBundle:Member");
        //send event remainders
        $organisations = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findAll();
        foreach ($organisations as $organisation) {
            $settings = $this->getDoctrine()->getRepository("AppBundle:OrganisationSetting")->getByOrganisation($organisation);

            $adminEmail = $settings->getReceiverOfRemainders() != null ? $settings->getReceiverOfRemainders()->getEmail() : null;

            $now = new \DateTime();
            if ($settings->getLastConfirmEventEmailSend() instanceof \DateTime) {
                $threshHold = clone($settings->getLastConfirmEventEmailSend());
            } else {
                //the first schedule is skipped
                $threshHold = new \DateTime("now");
            }
            //add -1 days + 22 hours because processing this also needs time (but probably not more than 2 hours)
            $threshHold->add(new \DateInterval("P" . ($settings->getSendConfirmEventEmailDays() - 1) . "DT22H"));
            $sendRemainderEmails = $threshHold <= $now;

            foreach ($organisation->getMembers() as $member) {
                /* @var Member $member */
                $unconfirmed = $memberRepo->countUnconfirmedEvents($member);
                $lateUnconfirmed = $memberRepo->countLateUnconfirmedEvents($member);
                if ($unconfirmed > 0 && $sendRemainderEmails && $lateUnconfirmed == 0) {
                    $unconfirmedEvents = $memberRepo->findUnconfirmedEvents($member);

                    $memberCount = 0;
                    /* @var Person[] $persons */
                    $persons = [];
                    $personCount = [];
                    foreach ($unconfirmedEvents as $unconfirmedEvent) {
                        if ($unconfirmedEvent->getPerson() == null) {
                            $memberCount++;
                        } else {
                            if (!isset($personCount[$unconfirmedEvent->getPerson()->getId()])) {
                                $personCount[$unconfirmedEvent->getPerson()->getId()] = 0;
                                $persons[$unconfirmedEvent->getPerson()->getId()] = $unconfirmedEvent->getPerson();
                            }
                        }
                    }

                    if ($memberCount > 0) {
                        //send email to member
                        $message = \Swift_Message::newInstance()
                            ->setSubject($trans->trans("member_event_confirm_remainder.subject", [], "email_cronjob"))
                            ->setFrom($this->getParameter("mailer_email"))
                            ->setTo($member->getEmail())
                            ->setBody($trans->trans(
                                "member_event_confirm_remainder.message",
                                [
                                    "%link%" => $this->generateUrl("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL),
                                    "%count%" => $unconfirmed
                                ],
                                "email_cronjob"));
                        $mailer->send($message);
                    }
                    foreach ($persons as $key => $val) {
                        //send remainder to person
                        $message = \Swift_Message::newInstance()
                            ->setSubject($trans->trans("member_event_confirm_remainder.subject", [], "email_cronjob"))
                            ->setFrom($this->getParameter("mailer_email"))
                            ->setTo($val->getEmail())
                            ->setBody($trans->trans(
                                "member_event_confirm_remainder.message",
                                [
                                    "%link%" => $this->generateUrl("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL),
                                    "%count%" => $personCount[$key]
                                ],
                                "email_cronjob"));
                        $mailer->send($message);
                    }
                }
                if ($lateUnconfirmed > 0) {
                    $lateUnconfirmedEvents = $memberRepo->findLateUnconfirmedEvents($member);
                    foreach ($lateUnconfirmedEvents as $lateUnconfirmedEvent) {
                        //send email both to admin & member, annoying him into confirming it
                        //send remainder
                        if ($lateUnconfirmedEvent->getPerson() instanceof Person) {
                            $owner = $lateUnconfirmedEvent->getPerson()->getFullName();
                            $ownerEmail = $lateUnconfirmedEvent->getPerson()->getEmail();
                        } else {
                            $owner = $lateUnconfirmedEvent->getMember()->getName();
                            $ownerEmail = $lateUnconfirmedEvent->getMember()->getEmail();
                        }

                        $message = \Swift_Message::newInstance()
                            ->setSubject($trans->trans("member_event_confirm_too_late_remainder.subject", [], "email_cronjob"))
                            ->setFrom($this->getParameter("mailer_email"))
                            ->setTo($ownerEmail)
                            ->setBody($trans->trans(
                                "member_event_confirm_too_late_remainder.message",
                                [
                                    "%link%" => $this->generateUrl("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL),
                                    "%event_short%" => $lateUnconfirmedEvent->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT) . " - " . $lateUnconfirmedEvent->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT),
                                    "%owner%" => $owner
                                ],
                                "email_cronjob"));

                        if ($adminEmail != null) {
                            $message->addCc($adminEmail);
                        }
                        $mailer->send($message);
                    }
                }
            }

            $settings->setLastConfirmEventEmailSend(new \DateTime());
            $this->fastSave($settings);
        }
        return new Response("finished");
    }

    /**
     * @Route("/weekly/{secret}", name="cron_weekly")
     * @param Request $request
     * @param $secret
     * @return Response
     */
    public function weeklyAction(Request $request, $secret)
    {
        return new Response($secret == $this->getParameter("secret") ? "successful" : "access denied");
    }
}