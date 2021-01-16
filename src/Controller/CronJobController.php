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
use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\Setting;
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
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return Response
     */
    public function dailyAction($secret, TranslatorInterface $translator, EmailService $emailService)
    {
        if ($secret !== $this->getParameter('APP_SECRET')) {
            return new Response('access denied');
        }

        $setting = $this->getDoctrine()->getRepository(Setting::class)->findSingle();
        $remainderEmailInterval = $setting->getSendRemainderDaysInterval();
        if (0 === date('z') % $remainderEmailInterval) {
            //get all events which might be a problemsupportMail
            $eventRepo = $this->getDoctrine()->getRepository(Event::class);
            $eventSearchModel = new SearchModel(SearchModel::NONE);
            $eventSearchModel->setEndDateTime(new \DateTime('now + '.($setting->getCanConfirmDaysAdvance() - $setting->getSendRemainderDaysInterval()).' days'));
            $eventSearchModel->setIsConfirmed(false);
            $events = $eventRepo->search($eventSearchModel);

            //count all events which need to be remainded
            $emailRemainder = [];
            foreach ($events as $event) {
                if (null !== $event->getDoctor()) {
                    ++$emailRemainder[$event->getDoctor()->getEmail()];
                } else {
                    ++$emailRemainder[$event->getClinic()->getEmail()];
                }
                $event->setLastRemainderEmailSent(new \DateTime());
                $this->fastSave($event);
            }

            //send remainders
            foreach ($emailRemainder as $email => $eventCount) {
                //send email to clinic
                $subject = $translator->trans('remainder.subject', [], 'cron');
                $body = $translator->trans('remainder.message', ['%count%' => $eventCount], 'cron');
                $actionText = $translator->trans('remainder.action_text', [], 'cron');
                $actionLink = $this->generateUrl('index_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $emailService->sendActionEmail($email, $subject, $body, $actionText, $actionLink);
            }
        }

        //send the daily, annoying remainders
        $mustConfirmBy = $setting->getMustConfirmDaysAdvance();

        //get all events which might be a problem
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $eventSearchModel = new SearchModel(SearchModel::NONE);
        $eventSearchModel->setEndDateTime(new \DateTime('now + '.$mustConfirmBy.' days'));
        $eventSearchModel->setIsConfirmed(false);
        $events = $eventRepo->search($eventSearchModel);

        //get admin emails
        $userRepo = $this->getDoctrine()->getRepository(Doctor::class);
        $admins = $userRepo->findBy(['isAdministrator' => true, 'receivesAdministratorMail' => true]);
        $adminEmails = [];
        foreach ($admins as $admin) {
            $adminEmails[] = $admin->getEmail();
        }

        //send an extra email for each late event
        foreach ($events as $event) {
            $targetEmail = null;
            $ownerName = null;
            if (null !== $event->getDoctor()) {
                $targetEmail = $event->getDoctor()->getEmail();
                $ownerName = $event->getDoctor()->getFullName();
            } else {
                $targetEmail = $event->getClinic()->getEmail();
                $ownerName = $event->getClinic()->getName();
            }

            //send email to clinic
            $subject = $translator->trans('too_late_remainder.subject', [], 'cron');
            $body = $translator->trans(
                'too_late_remainder.message',
                [
                    '%event_short%' => $event->toShort(),
                    '%owner%' => $ownerName.' ('.$targetEmail.')',
                ],
                'cron'
            );
            $actionText = $translator->trans('too_late_remainder.action_text', [], 'cron');
            $actionLink = $this->generateUrl('index_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $emailService->sendActionEmail($targetEmail, $subject, $body, $actionText, $actionLink, $adminEmails);

            $event->setLastRemainderEmailSent(new \DateTime());
            $this->fastSave($event);
        }

        return new Response('finished');
    }
}
