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
use App\Service\EmailService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[\Symfony\Component\Routing\Attribute\Route(path: '/trade')]
class TradeController extends BaseFormController
{
    #[\Symfony\Component\Routing\Attribute\Route(path: '/', name: 'trade_index')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('trade/index.html.twig');
    }

    /**
     * @throws \Exception
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/accept/{eventOffer}', name: 'trade_accept')]
    public function accept(EventOffer $eventOffer, TranslatorInterface $translator, EmailService $emailService): \Symfony\Component\HttpFoundation\RedirectResponse
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
            $manager = $this->getDoctrine()->getManager();
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
    #[\Symfony\Component\Routing\Attribute\Route(path: '/decline/{eventOffer}', name: 'trade_decline')]
    public function decline(EventOffer $eventOffer, TranslatorInterface $translator, EmailService $emailService): \Symfony\Component\HttpFoundation\RedirectResponse
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
            $this->fastSave($eventOffer);
        } else {
            $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
        }

        return $this->redirectToRoute('index_index');
    }

    #[\Symfony\Component\Routing\Attribute\Route(path: '/acknowledge/{eventOffer}', name: 'trade_acknowledge')]
    public function acknowledge(EventOffer $eventOffer, TranslatorInterface $translator): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if ($eventOffer->acknowledge($this->getUser())) {
            $this->displaySuccess($translator->trans('acknowledge.success.trade_acknowledged', [], 'trade'));
            $eventOffer->tryMarkAsResolved();
            $this->fastSave($eventOffer);
        } else {
            $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
        }

        return $this->redirectToRoute('index_index');
    }

    #[\Symfony\Component\Routing\Attribute\Route(path: '/withdraw/{eventOffer}', name: 'trade_withdraw')]
    public function withdraw(EventOffer $eventOffer, TranslatorInterface $translator): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } elseif ($eventOffer->withdraw($this->getUser())) {
            $this->displaySuccess($translator->trans('withdraw.success.trade_withdrawn', [], 'trade'));
            $eventOffer->tryMarkAsResolved();
            $this->fastSave($eventOffer);
        } else {
            $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
        }

        return $this->redirectToRoute('index_index');
    }
}
