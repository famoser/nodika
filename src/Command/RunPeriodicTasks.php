<?php

declare(strict_types=1);

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\Setting;
use App\Helper\DoctrineHelper;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\EmailServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RunPeriodicTasks extends Command
{
    use LockableTrait;

    public function __construct(private readonly ManagerRegistry $doctrine, private readonly TranslatorInterface $translator, private readonly RouterInterface $router, private readonly EmailServiceInterface $emailService, private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:run-periodic-tasks:daily')
            // the short description shown while running "php bin/console list"
            ->setDescription('Runs period daily tasks, e.g. dispatching reminder emails.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $setting = $this->doctrine->getRepository(Setting::class)->findSingle();
        $remainderEmailInterval = $setting->getSendRemainderDaysInterval();
        if (0 === date('z') % $remainderEmailInterval) {
            // get all events which might be a problem
            $eventRepo = $this->doctrine->getRepository(Event::class);
            $eventSearchModel = new SearchModel(SearchModel::NONE);
            $eventSearchModel->setEndDateTime(new \DateTime('now + '.($setting->getCanConfirmDaysAdvance() - $setting->getSendRemainderDaysInterval()).' days'));
            $eventSearchModel->setIsConfirmed(false);
            $events = $eventRepo->search($eventSearchModel);

            // count all events which need to be remainded
            $emailRemainder = [];
            foreach ($events as $event) {
                if (null !== $event->getDoctor()) {
                    ++$emailRemainder[$event->getDoctor()->getEmail()];
                } else {
                    ++$emailRemainder[$event->getClinic()->getEmail()];
                }
                $event->setLastRemainderEmailSent(new \DateTime());
                DoctrineHelper::persistAndFlush($this->doctrine, $event);
            }

            // send remainders
            foreach ($emailRemainder as $email => $eventCount) {
                // send email to clinic
                $subject = $this->translator->trans('remainder.subject', [], 'cron');
                $body = $this->translator->trans('remainder.message', ['%count%' => $eventCount], 'cron');
                $actionText = $this->translator->trans('remainder.action_text', [], 'cron');
                $actionLink = $this->router->generate('index_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $this->emailService->sendActionEmail($email, $subject, $body, $actionText, $actionLink);
            }
        }

        // send the daily, annoying remainders
        $mustConfirmBy = $setting->getMustConfirmDaysAdvance();

        // get all events which might be a problem
        $eventRepo = $this->doctrine->getRepository(Event::class);
        $eventSearchModel = new SearchModel(SearchModel::NONE);
        $eventSearchModel->setEndDateTime(new \DateTime('now + '.$mustConfirmBy.' days'));
        $eventSearchModel->setIsConfirmed(false);
        $events = $eventRepo->search($eventSearchModel);

        // get admin emails
        $userRepo = $this->doctrine->getRepository(Doctor::class);
        $admins = $userRepo->findBy(['isAdministrator' => true, 'receivesAdministratorMail' => true]);
        $adminEmails = [];
        foreach ($admins as $admin) {
            $adminEmails[] = $admin->getEmail();
        }

        // send an extra email for each late event
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

            // send email to clinic
            $subject = $this->translator->trans('too_late_remainder.subject', [], 'cron');
            $body = $this->translator->trans(
                'too_late_remainder.message',
                [
                    '%event_short%' => $event->toShort(),
                    '%owner%' => $ownerName.' ('.$targetEmail.')',
                ],
                'cron'
            );
            $actionText = $this->translator->trans('too_late_remainder.action_text', [], 'cron');
            $actionLink = $this->router->generate('index_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->emailService->sendActionEmail($targetEmail, $subject, $body, $actionText, $actionLink, $adminEmails);

            $event->setLastRemainderEmailSent(new \DateTime());
            DoctrineHelper::persistAndFlush($this->doctrine, $event);
        }

        $message = 'Reminders sent for '.count($events).' events.';
        $this->logger->info($message);
        $output->writeln($message);

        return Command::SUCCESS;
    }
}
