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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/trade")
 */
class TradeController extends BaseFormController
{
    /**
     * @Route("/", name="trade_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('trade/index.html.twig');
    }

    /**
     * @Route("/accept/{eventOffer}", name="trade_accept")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Exception
     */
    public function acceptAction(EventOffer $eventOffer, TranslatorInterface $translator, EmailService $emailService)
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } else {
            if ($eventOffer->canExecute()) {
                $this->displaySuccess($translator->trans('accept.success.trade_already_accepted', [], 'trade'));
            } elseif (!$eventOffer->accept($this->getUser())) {
                $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
            } else {
                if (!$eventOffer->canExecute()) {
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
            }
        }

        return $this->redirectToRoute('index_index');
    }

    /**
     * @Route("/decline/{eventOffer}", name="trade_decline")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Exception
     */
    public function declineAction(EventOffer $eventOffer, TranslatorInterface $translator, EmailService $emailService)
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } else {
            if ($eventOffer->decline($this->getUser())) {
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
        }

        return $this->redirectToRoute('index_index');
    }

    /**
     * @Route("/acknowledge/{eventOffer}", name="trade_acknowledge")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function acknowledgeAction(EventOffer $eventOffer, TranslatorInterface $translator)
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

    /**
     * @Route("/withdraw/{eventOffer}", name="trade_withdraw")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function withdrawAction(EventOffer $eventOffer, TranslatorInterface $translator)
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } else {
            if ($eventOffer->withdraw($this->getUser())) {
                $this->displaySuccess($translator->trans('withdraw.success.trade_withdrawn', [], 'trade'));
                $eventOffer->tryMarkAsResolved();
                $this->fastSave($eventOffer);
            } else {
                $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
            }
        }

        return $this->redirectToRoute('index_index');
    }
}
