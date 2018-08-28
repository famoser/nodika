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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @param EventOffer          $eventOffer
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function acceptAction(EventOffer $eventOffer, TranslatorInterface $translator)
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } else {
            //accept
            if (!$eventOffer->accept($this->getUser())) {
                $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
            } else {
                if (!$eventOffer->canExecute()) {
                    $this->displaySuccess($translator->trans('accept.success.trade_accepted', [], 'trade'));
                } else {
                    //execute trade
                    $manager = $this->getDoctrine()->getManager();
                    foreach ($eventOffer->getSenderOwnedEvents() as $senderOwnedEvent) {
                        $senderOwnedEvent->setClinic($eventOffer->getReceiverClinic());
                        $senderOwnedEvent->setDoctor($eventOffer->getReceiver());

                        //save history
                        $eventPast = EventPast::create($senderOwnedEvent, EventChangeType::TRADED_TO_NEW_CLINIC, $eventOffer->getReceiver());
                        $manager->persist($eventPast);
                    }
                    foreach ($eventOffer->getReceiverOwnedEvents() as $receiverOwnedEvent) {
                        $receiverOwnedEvent->setClinic($eventOffer->getSenderClinic());
                        $receiverOwnedEvent->setDoctor($eventOffer->getSender());

                        //save history
                        $eventPast = EventPast::create($receiverOwnedEvent, EventChangeType::TRADED_TO_NEW_CLINIC, $eventOffer->getSender());
                        $manager->persist($eventPast);
                    }
                    $manager->flush();

                    $this->displaySuccess($translator->trans('accept.success.trade_executed', [], 'trade'));
                }
            }
        }

        return $this->redirectToRoute('index_index');
    }

    /**
     * @Route("/decline/{eventOffer}", name="trade_decline")
     *
     * @param EventOffer          $eventOffer
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function declineAction(EventOffer $eventOffer, TranslatorInterface $translator)
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } else {
            if ($eventOffer->decline($this->getUser())) {
                $this->displaySuccess($translator->trans('decline.success.trade_decline', [], 'trade'));
                $eventOffer->tryMarkAsResolved();
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
     * @param EventOffer          $eventOffer
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function acknowledgeAction(EventOffer $eventOffer, TranslatorInterface $translator)
    {
        if (!$eventOffer->isValid()) {
            $this->displayError($translator->trans('accept.danger.invalid', [], 'trade'));
        } else {
            if ($eventOffer->acknowledge($this->getUser())) {
                $this->displaySuccess($translator->trans('acknowledge.success.trade_acknowledged', [], 'trade'));
                $eventOffer->tryMarkAsResolved();
                $this->fastSave($eventOffer);
            } else {
                $this->displayError($translator->trans('accept.danger.action_invalid', [], 'trade'));
            }
        }

        return $this->redirectToRoute('index_index');
    }

    /**
     * @Route("/withdraw/{eventOffer}", name="trade_withdraw")
     *
     * @param EventOffer          $eventOffer
     * @param TranslatorInterface $translator
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
