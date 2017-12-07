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
                        $this->get("app.email_service")->sendConfirmLate($unconfirmedEvent, $adminEmail);
                    } else {

                        $disable =
                            //disable email if already sent
                            ($unconfirmedEvent->getLastRemainderEmailSent() != null && $unconfirmedEvent->getLastRemainderEmailSent() > $remainderSendBlock);

                        $memberRemainderCount++;
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

                if ($sendRemainderToMember) {
                    //send email to member
                    $this->get("app.email_service")->sendScheduledConfirmToMember($member, $memberRemainderCount);

                    foreach ($unconfirmedEvents as $unconfirmedEvent) {
                        if ($unconfirmedEvent->getPerson() == null) {
                            $unconfirmedEvent->setLastRemainderEmailSent(new \DateTime());
                            $manager->persist($unconfirmedEvent);
                        }
                    }
                }

                foreach ($sendRemainderToPerson as $person) {
                    if (isset($sendRemainderToPersonDisabled[$person->getId()])) {
                        //skip
                        continue;
                    }

                    $this->get("app.email_service")->sendScheduledConfirmToPerson($person, $personRemainderCount[$person->getId()]);


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
    public
    function weeklyAction(Request $request, $secret)
    {
        return new Response($secret == $this->getParameter("secret") ? "successful" : "access denied");
    }
}