<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseDoctrineController;
use App\Entity\Event;
use App\Model\Event\SearchModel;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/cron")
 */
class CronJobController extends BaseDoctrineController
{
    /**
     * @Route("/test/{secret}", name="cron_test")
     *
     * @param $secret
     *
     * @return Response
     */
    public function testAction($secret)
    {
        return new Response($secret === $this->getParameter('APP_SECRET') ? 'successful' : 'access denied');
    }

    /**
     * @Route("/daily/{secret}", name="cron_daily")
     *
     * @param $secret
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     *
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function dailyAction($secret, TranslatorInterface $translator, EmailService $emailService)
    {
        if ($secret !== $this->getParameter('APP_SECRET')) {
            return new Response('access denied');
        }

        $remainderEmailInterval = $this->getParameter('REMAINDER_EMAIL_INTERVAL');
        if (date('z') % $remainderEmailInterval == 0) {
            //send regular remainders
            $sendRemainderBy = $this->getParameter('SEND_REMAINDER_BY');

            //get all events which might be a problem
            $eventRepo = $this->getDoctrine()->getRepository(Event::class);
            $eventSearchModel = new SearchModel();
            $eventSearchModel->setStartDateTime(new \DateTime());
            $eventSearchModel->setEndDateTime(new \DateTime("now + " . $sendRemainderBy . " days"));
            $eventSearchModel->setIsConfirmed(false);
            $events = $eventRepo->search($eventSearchModel);

            //count all events which need to be remainded
            $emailRemainder = [];
            foreach ($events as $event) {
                if ($event->getFrontendUser() != null) {
                    $emailRemainder[$event->getFrontendUser()->getEmail()]++;
                } else {
                    $emailRemainder[$event->getMember()->getEmail()]++;
                }
                $event->setLastRemainderEmailSent(new \DateTime());
                $this->fastSave($event);
            }

            //send remainders
            foreach ($emailRemainder as $email => $eventCount) {
                //send email to member
                $subject = $translator->trans('remainder.subject', [], 'email_cronjob');
                $body = $translator->trans('remainder.message', ['%count%' => $eventCount], 'email_cronjob');
                $actionText = $translator->trans('remainder.action_text', [], 'email_cronjob');
                $actionLink = $this->generateUrl('event_confirm', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $emailService->sendActionEmail($email, $subject, $body, $actionText, $actionLink);
            }
        }

        //send the daily, annoying remainders
        $mustConfirmBy = $this->getParameter('MUST_CONFIRM_EVENT_BY');

        //get all events which might be a problem
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $eventSearchModel = new SearchModel();
        $eventSearchModel->setStartDateTime(new \DateTime());
        $eventSearchModel->setEndDateTime(new \DateTime("now + " . $mustConfirmBy . " days"));
        $eventSearchModel->setIsConfirmed(false);
        $events = $eventRepo->search($eventSearchModel);


        //get admin emails
        $userRepo = $this->getDoctrine()->getRepository('App:FrontendUser');
        $admins = $userRepo->findBy(["isAdministrator" => true]);
        $adminEmails = [];
        foreach ($admins as $admin) {
            $adminEmails[] = $admin->getEmail();
        }


        //send an extra email for each late event
        foreach ($events as $event) {
            $targetEmail = null;
            $ownerName = null;
            if ($event->getFrontendUser() != null) {
                $targetEmail = $event->getFrontendUser()->getEmail();
                $ownerName = $event->getFrontendUser()->getFullName();
            } else {
                $targetEmail = $event->getMember()->getEmail();
                $ownerName = $event->getMember()->getName();
            }

            //send email to member
            $subject = $translator->trans('too_late_remainder.subject', [], 'email_cronjob');
            $body = $translator->trans(
                'too_late_remainder.message',
                [
                    '%event_short%' => $event->toShort(),
                    '%owner%' => $ownerName . " (" . $targetEmail . ")"
                ],
                'email_cronjob'
            );
            $actionText = $translator->trans('too_late_remainder.action_text', [], 'email_cronjob');
            $actionLink = $this->generateUrl('event_confirm', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $emailService->sendActionEmail($targetEmail, $subject, $body, $actionText, $actionLink, $adminEmails);

            $event->setLastRemainderEmailSent(new \DateTime());
            $this->fastSave($event);
        }

        return new Response('finished');
    }
}
