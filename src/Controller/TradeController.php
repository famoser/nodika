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

use App\Controller\Base\BaseFormController;
use App\Entity\EventOffer;
use App\Entity\EventPast;
use App\Enum\EventChangeType;
use App\Helper\DoctrineHelper;
use App\Service\EmailService;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/trade')]
class TradeController extends BaseFormController
{
    #[Route(path: '/', name: 'trade_index')]
    public function index(): Response
    {
        return $this->render('trade/index.html.twig');
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/accept/{eventOffer}', name: 'trade_accept')]
    public function accept(EventOffer $eventOffer, ManagerRegistry $registry, TranslatorInterface $translator, EmailService $emailService): RedirectResponse
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } elseif ($eventOffer->canExecute()) {
            $this->displaySuccess($translator->trans('accept.success.trade_already_accepted', [], 'trade'));
        } elseif (!$eventOffer->accept($this->getUser())) {
            $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
        } elseif (!$eventOffer->canExecute()) {
            $this->displaySuccess($translator->trans('accept.success.trade_accepted', [], 'trade'));
        } else {
            // execute trade
            $manager = $registry->getManager();
            foreach ($eventOffer->getSenderOwnedEvents() as $senderOwnedEvent) {
                $senderOwnedEvent->setClinic($eventOffer->getReceiverClinic());
                $senderOwnedEvent->setDoctor($eventOffer->getReceiver());
                $senderOwnedEvent->undoConfirm();

                // save history
                $eventPast = EventPast::create($senderOwnedEvent, EventChangeType::TRADED_TO_NEW_OWNER, $eventOffer->getReceiver());
                $manager->persist($eventPast);
            }
            foreach ($eventOffer->getReceiverOwnedEvents() as $receiverOwnedEvent) {
                $receiverOwnedEvent->setClinic($eventOffer->getSenderClinic());
                $receiverOwnedEvent->setDoctor($eventOffer->getSender());
                $receiverOwnedEvent->undoConfirm();

                // save history
                $eventPast = EventPast::create($receiverOwnedEvent, EventChangeType::TRADED_TO_NEW_OWNER, $eventOffer->getSender());
                $manager->persist($eventPast);
            }
            $manager->persist($eventOffer);
            $manager->flush();

            // inform sender
            $emailService->sendActionEmail(
                $eventOffer->getReceiver()->getEmail(),
                $translator->trans('emails.offer_accepted.subject', [], 'trade'),
                $translator->trans('emails.offer_accepted.message', [], 'trade'),
                $translator->trans('emails.offer_accepted.action_text', [], 'trade'),
                $this->generateUrl('index_index', [], UrlGeneratorInterface::ABSOLUTE_URL));

            $this->displaySuccess($translator->trans('accept.success.trade_executed', [], 'trade'));
        }

        return $this->redirectToRoute('index_index');
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/decline/{eventOffer}', name: 'trade_decline')]
    public function decline(EventOffer $eventOffer, ManagerRegistry $registry, TranslatorInterface $translator, EmailService $emailService): RedirectResponse
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } elseif ($eventOffer->decline($this->getUser())) {
            $this->displaySuccess($translator->trans('decline.success.trade_decline', [], 'trade'));
            $eventOffer->tryMarkAsResolved();
            // inform sender
            $emailService->sendActionEmail(
                $eventOffer->getReceiver()->getEmail(),
                $translator->trans('emails.offer_declined.subject', [], 'trade'),
                $translator->trans('emails.offer_declined.message', [], 'trade'),
                $translator->trans('emails.offer_declined.action_text', [], 'trade'),
                $this->generateUrl('index_index', [], UrlGeneratorInterface::ABSOLUTE_URL));
            DoctrineHelper::persistAndFlush($registry, ...[$eventOffer]);
        } else {
            $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
        }

        return $this->redirectToRoute('index_index');
    }

    #[Route(path: '/acknowledge/{eventOffer}', name: 'trade_acknowledge')]
    public function acknowledge(EventOffer $eventOffer, ManagerRegistry $registry, TranslatorInterface $translator): RedirectResponse
    {
        if ($eventOffer->acknowledge($this->getUser())) {
            $this->displaySuccess($translator->trans('acknowledge.success.trade_acknowledged', [], 'trade'));
            $eventOffer->tryMarkAsResolved();
            DoctrineHelper::persistAndFlush($registry, ...[$eventOffer]);
        } else {
            $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
        }

        return $this->redirectToRoute('index_index');
    }

    #[Route(path: '/withdraw/{eventOffer}', name: 'trade_withdraw')]
    public function withdraw(EventOffer $eventOffer, ManagerRegistry $registry, TranslatorInterface $translator): RedirectResponse
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } elseif ($eventOffer->withdraw($this->getUser())) {
            $this->displaySuccess($translator->trans('withdraw.success.trade_withdrawn', [], 'trade'));
            $eventOffer->tryMarkAsResolved();
            DoctrineHelper::persistAndFlush($registry, ...[$eventOffer]);
        } else {
            $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
        }

        return $this->redirectToRoute('index_index');
    }
}
