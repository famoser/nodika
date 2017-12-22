<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace App\Controller;


use App\Controller\Base\BaseFrontendController;
use App\Entity\Member;
use App\Entity\Person;
use App\Helper\DateTimeFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/cron")
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

        $translator = $this->get("translator");
        $memberRepo = $this->getDoctrine()->getRepository("App:Member");
        //send event remainders
        $organisations = $this->getDoctrine()->getRepository("App:Organisation")->findAll();
        foreach ($organisations as $organisation) {
            $settings = $this->getDoctrine()->getRepository("App:OrganisationSetting")->getByOrganisation($organisation);
            $adminEmail = $settings->getReceiverOfRemainders() != null ? $settings->getReceiverOfRemainders()->getEmail() : null;

            //first time email is skipped
            $remainderThreshold = new \DateTime("now + " . ($settings->getCanConfirmEventBeforeDays() - $settings->getSendConfirmEventEmailDays()) . " days");
            $tooLateThreshold = new \DateTime("now + " . $settings->getMustConfirmEventBeforeDays() . " days");
            $remainderSendBlock = new \DateTime("now - " . $settings->getSendConfirmEventEmailDays() . " days");

            foreach ($organisation->getMembers() as $member) {
                /* @var Member $member */
                $unconfirmedEvents = $memberRepo->findUnconfirmedEventsByMember($member, $remainderThreshold);

                $memberRemainderCount = 0;
                $sendRemainderToMember = true;

                /* @var Person[] $sendRemainderToPerson */
                $sendRemainderToPerson = [];
                $personRemainderCount = [];
                $sendRemainderToPersonDisabled = [];
                foreach ($unconfirmedEvents as $unconfirmedEvent) {
                    if ($unconfirmedEvent->getStartDateTime() < $tooLateThreshold) {
                        //member has confirmed too late!

                        $receiver = null;
                        $member = $unconfirmedEvent->getMember();
                        if ($unconfirmedEvent->getPerson() != null) {
                            $receiver = $unconfirmedEvent->getPerson()->getEmail();
                            $owner = $unconfirmedEvent->getPerson()->getFullName();
                        } else {
                            $receiver = $member->getEmail();
                            $owner = $member->getName();
                        }

                        $body = $translator->trans(
                            "member_event_confirm_too_late_remainder.message",
                            [
                                "%event_short%" =>
                                    $unconfirmedEvent->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT) .
                                    " - " .
                                    $unconfirmedEvent->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT),
                                "%owner%" => $owner
                            ],
                            "email_cronjob");

                        $subject = $translator->trans("member_event_confirm_too_late_remainder.subject", [], "email_cronjob");
                        $actionText = $translator->trans("member_event_confirm_too_late_remainder.action_text", [], "email_cronjob");
                        $actionLink = $this->generateUrl("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL);
                        $this->get("app.email_service")->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink, $adminEmail);
                    } else {

                        $disable =
                            //disable email if already sent
                            ($unconfirmedEvent->getLastRemainderEmailSent() != null && $unconfirmedEvent->getLastRemainderEmailSent() > $remainderSendBlock);

                        if ($unconfirmedEvent->getPerson() != null) {
                            $person = $unconfirmedEvent->getPerson();

                            $sendRemainderToPerson[$person->getId()] = $person;

                            if (!isset($personRemainderCount[$person->getId()])) {
                                $personRemainderCount[$person->getId()] = 0;
                            }
                            $personRemainderCount[$person->getId()]++;

                            if ($disable) {
                                $sendRemainderToPersonDisabled[$person->getId()] = true;
                            }
                        } else {
                            $memberRemainderCount++;
                            if ($disable) {
                                $sendRemainderToMember = false;
                            }
                        }
                    }
                }

                $manager = $this->getDoctrine()->getManager();

                if ($sendRemainderToMember && $memberRemainderCount > 0) {
                    //send email to member
                    $subject = $translator->trans("member_event_confirm_remainder.subject", [], "email_cronjob");
                    $receiver = $member->getEmail();
                    $body = $translator->trans(
                        "member_event_confirm_remainder.message",
                        ["%count%" => $memberRemainderCount],
                        "email_cronjob"
                    );

                    $actionText = $translator->trans("member_event_confirm_too_late_remainder.action_text", [], "email_cronjob");
                    $actionLink = $this->generateUrl("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $this->get("app.email_service")->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink);

                    foreach ($unconfirmedEvents as $unconfirmedEvent) {
                        if ($unconfirmedEvent->getPerson() == null) {
                            $unconfirmedEvent->setLastRemainderEmailSent(new \DateTime());
                            $manager->persist($unconfirmedEvent);
                        }
                    }
                }


                foreach ($sendRemainderToPerson as $person) {
                    if (isset($sendRemainderToPersonDisabled[$person->getId()]) || !isset($personRemainderCount[$person->getId()]) || !($personRemainderCount[$person->getId()] > 0)) {
                        //skip
                        continue;
                    }

                    $receiver = $person->getEmail();
                    $subject = $translator->trans("member_event_confirm_remainder.subject", [], "email_cronjob");
                    $body = $translator->trans("member_event_confirm_remainder.message",
                        ["%count%" => $personRemainderCount[$person->getId()]],
                        "email_cronjob"
                    );
                    $actionText = $translator->trans("member_event_confirm_remainder.action_text", [], "email_cronjob");
                    $actionLink = $this->generateUrl("event_confirm", [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $this->get("app.email_service")->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink);

                    foreach ($unconfirmedEvents as $unconfirmedEvent) {
                        if ($unconfirmedEvent->getPerson() != null && $unconfirmedEvent->getPerson()->getId() == $person->getId()) {
                            $unconfirmedEvent->setLastRemainderEmailSent(new \DateTime());
                            $manager->persist($unconfirmedEvent);
                        }
                    }
                }

                $manager->flush();
            }
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