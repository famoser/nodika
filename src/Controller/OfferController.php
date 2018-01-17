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

use App\Controller\Base\BaseFrontendController;
use App\Entity\Event;
use App\Entity\EventOffer;
use App\Entity\EventOfferEntry;
use App\Entity\Member;
use App\Entity\Person;
use App\Enum\EventChangeType;
use App\Enum\OfferStatus;
use App\Enum\TradeTag;
use App\Helper\DateTimeConverter;
use App\Model\Event\SearchEventModel;
use App\Service\EmailService;
use App\Service\EventPastEvaluationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/offer")
 * @Security("has_role('ROLE_USER')")
 */
class OfferController extends BaseFrontendController
{
    /**
     * @Route("/", name="offer_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $repo = $this->getDoctrine()->getRepository('App:EventOffer');
        $arr['author_of_offers'] = $repo->findBy(['offeredByMember' => $member->getId(), 'offeredByPerson' => $this->getPerson()->getId()]);
        $arr['accepted_offers'] = $repo->findBy(['offeredToMember' => $member->getId(), 'offeredToPerson' => $this->getPerson()->getId(), 'status' => OfferStatus::ACCEPTED]);
        $arr['open_offers'] = $repo->findBy(['offeredToMember' => $member->getId(), 'offeredToPerson' => $this->getPerson()->getId(), 'status' => OfferStatus::OPEN]);

        return $this->renderWithBackUrl('offer/index.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }

    /**
     * @Route("/new", name="offer_new")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $memberColl = $member->getOrganisation()->getMembers();
        $memberColl->removeElement($member);
        $arr['members'] = $memberColl;

        return $this->renderWithBackUrl('offer/new.html.twig', $arr, $this->generateUrl('offer_index'));
    }

    /**
     * @Route("/start/{member}/{person}", name="offer_start")
     *
     * @param Member $member
     * @param Person $person
     *
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startAction(Member $member, Person $person, TranslatorInterface $translator)
    {
        $ownMember = $this->getMember();
        if (null === $ownMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        if ($ownMember->getOrganisation()->getId() !== $member->getOrganisation()->getId() ||
            !$member->getPersons()->contains($person)) {
            $this->displaySuccess($translator->trans('messages.no_access_anymore', [], 'offer'));

            return $this->redirectToRoute('offer_index');
        }

        $eventOffer = new EventOffer();
        $eventOffer->setCreateDateTime(new \DateTime());
        $eventOffer->setOfferedByMember($ownMember);
        $eventOffer->setOfferedByPerson($this->getPerson());
        $eventOffer->setOfferedToMember($member);
        $eventOffer->setOfferedToPerson($person);
        $this->fastSave($eventOffer);

        return $this->redirectToRoute('offer_choose_events', ['eventOffer' => $eventOffer->getId()]);
    }

    /**
     * @Route("/{eventOffer}/choose_events", name="offer_choose_events")
     *
     * @param Request $request
     * @param EventOffer $eventOffer
     *
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chooseEventsAction(Request $request, EventOffer $eventOffer, TranslatorInterface $translator, EmailService $emailService)
    {
        $ownMember = $this->getMember();
        if (null === $ownMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        if ($ownMember->getId() !== $eventOffer->getOfferedByMember()->getId() ||
            !in_array($eventOffer->getStatus(), [OfferStatus::CREATING, OfferStatus::OPEN, OfferStatus::CLOSED], true)) {
            $this->displaySuccess($translator->trans('messages.no_access_anymore', [], 'offer'));

            return $this->redirectToRoute('offer_index');
        }

        $repo = $this->getDoctrine()->getRepository('App:Organisation');
        $settingRepo = $this->getDoctrine()->getRepository('App:OrganisationSetting');
        $eventRepo = $this->getDoctrine()->getRepository('App:Event');

        if ('POST' === $request->getMethod()) {
            $events = [];
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'event_')) {
                    $eventId = mb_substr($key, 6); //cut off event_
                    /* @var Event $event */
                    $event = $eventRepo->find($eventId);

                    $isValidPossibility1 =
                        $event->getMember()->getId() === $eventOffer->getOfferedByMember()->getId() &&
                        $event->getPerson()->getId() === $eventOffer->getOfferedByPerson()->getId();

                    $isValidPossibility2 =
                        $event->getMember()->getId() === $eventOffer->getOfferedToMember()->getId() &&
                        $event->getPerson()->getId() === $eventOffer->getOfferedToPerson()->getId();
                    if ($isValidPossibility1 || $isValidPossibility2) {
                        $events[] = $event;
                    }
                } elseif ('description' === $key) {
                    $eventOffer->setDescription($value);
                }
            }
            $em = $this->getDoctrine()->getManager();
            foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
                $em->remove($eventOfferEntry);
            }

            foreach ($events as $event) {
                $eventOfferEntry = new EventOfferEntry();
                $eventOfferEntry->setEvent($event);
                $eventOfferEntry->setEventOffer($eventOffer);
                $em->persist($eventOfferEntry);
            }
            $eventOffer->setStatus(OfferStatus::OPEN);
            $eventOffer->setOpenDateTime(new \DateTime());
            $em->persist($eventOffer);
            $em->flush();

            $this->displaySuccess($translator->trans('messages.offer_open', [], 'offer'));

            $receiver = $eventOffer->getOfferedToPerson()->getEmail();
            $body = $translator->trans('emails.new_offer.message', [], 'offer');
            $subject = $translator->trans('emails.new_offer.subject', [], 'offer');
            $actionText = $translator->trans('emails.new_offer.action_text', [], 'offer');
            $actionLink = $this->generateUrl('offer_review', ['eventOffer' => $eventOffer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $emailService->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink);

            return $this->redirectToRoute('offer_index');
        }

        $organisationSettings = $settingRepo->getByOrganisation($ownMember->getOrganisation());
        $threshHold = DateTimeConverter::addDays(new \DateTime(), $organisationSettings->getTradeEventDays());
        $myEvents = new SearchEventModel($ownMember->getOrganisation(), $threshHold);
        $myEvents->setFilterMember($eventOffer->getOfferedByMember());
        $myEvents->setFilterPerson($eventOffer->getOfferedByPerson());
        $arr['myEventLineModels'] = $repo->findEventLineModels($myEvents);

        $theirEvents = new SearchEventModel($ownMember->getOrganisation(), $threshHold);
        $theirEvents->setFilterMember($eventOffer->getOfferedToMember());
        $theirEvents->setFilterPerson($eventOffer->getOfferedToPerson());
        $arr['theirEventLineModels'] = $repo->findEventLineModels($theirEvents);

        $arr['description_form'] = $eventOffer->getDescription();

        $offered = [];
        foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
            $offered[] = $eventOfferEntry->getEvent()->getId();
        }
        $arr['offered'] = $offered;

        $invalids = $this->getInvalidEventOfferEntries($eventOffer, false);
        if (count($invalids) > 0) {
            $this->displayError($translator->trans('messages.has_invalid_entries', [], 'offer'));
            $arr['invalids'] = $invalids;
        }

        $arr['eventOffer'] = $eventOffer;

        return $this->renderWithBackUrl('offer/choose_events.html.twig', $arr, $this->generateUrl('offer_index'));
    }

    /**
     * @Route("/{eventOffer}/review", name="offer_review")
     *
     * @param EventOffer $eventOffer
     *
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reviewAction(EventOffer $eventOffer, TranslatorInterface $translator)
    {
        $ownMember = $this->getMember();
        if (null === $ownMember) {
            return $this->redirectToRoute('dashboard_index');
        }
        $ownPerson = $this->getPerson();

        $acceptReject = $this->canAcceptOrRejectOffer($ownMember, $ownPerson, $eventOffer);
        $close = $this->canCloseOffer($ownMember, $ownPerson, $eventOffer);
        if (!($acceptReject || $close)) {
            $this->displaySuccess($translator->trans('messages.no_access_anymore', [], 'offer'));

            return $this->redirectToRoute('offer_index');
        }

        $invalids = $this->getInvalidEventOfferEntries($eventOffer, false);
        if (count($invalids) > 0) {
            if ($close) {
                //can close, therefore can edit
                return $this->redirectToRoute('offer_choose_events', ['eventOffer' => $eventOffer->getId()]);
            }
            $eventOffer->setStatus(OfferStatus::CREATING);
            $this->fastSave($eventOffer);
            $this->displayError($translator->trans('messages.has_invalid_entries_rejected', [], 'offer'));

            return $this->redirectToRoute('offer_index');
        }

        $arr['acceptRejectActions'] = $acceptReject;
        $arr['closeActions'] = $close;

        if ($eventOffer->getOfferedByPerson()->getId() !== $ownPerson->getId()) {
            $otherPersonId = $eventOffer->getOfferedByPerson()->getId();
        } else {
            $otherPersonId = $eventOffer->getOfferedToPerson()->getId();
        }

        /* @var Event[] $myEvents */
        $myEvents = [];
        /* @var Event[] $otherEvents */
        $otherEvents = [];

        foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
            if ($eventOfferEntry->getEvent()->getPerson()->getId() === $otherPersonId) {
                $otherEvents[] = $eventOfferEntry->getEvent();
            } elseif ($eventOfferEntry->getEvent()->getPerson()->getId() === $ownPerson->getId()) {
                $myEvents[] = $eventOfferEntry->getEvent();
            }
        }

        $arr['myEvents'] = $myEvents;
        $arr['otherEvents'] = $otherEvents;
        $arr['invalids'] = $this->getInvalidEventOfferEntries($eventOffer, false);
        $arr['eventOffer'] = $eventOffer;

        return $this->renderWithBackUrl('offer/review.html.twig', $arr, $this->generateUrl('offer_index'));
    }

    /**
     * @Route("/{eventOffer}/accept", name="offer_accept")
     *
     * @param EventOffer $eventOffer
     *
     * @param TranslatorInterface $translator
     * @param EventPastEvaluationService $eventPastEvaluationService
     * @param EmailService $emailService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function acceptAction(EventOffer $eventOffer, TranslatorInterface $translator, EventPastEvaluationService $eventPastEvaluationService, EmailService $emailService)
    {
        $ownMember = $this->getMember();
        if (null === $ownMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        $ownPerson = $this->getPerson();

        $acceptReject = $this->canAcceptOrRejectOffer($ownMember, $ownPerson, $eventOffer);
        if ($acceptReject) {
            $invalids = $this->getInvalidEventOfferEntries($eventOffer, false);
            if (count($invalids) > 0) {
                $eventOffer->setStatus(OfferStatus::CREATING);
                $this->fastSave($eventOffer);
                $this->displayError($translator->trans('messages.has_invalid_entries_rejected', [], 'offer'));

                return $this->redirectToRoute('offer_index');
            }

            $em = $this->getDoctrine()->getManager();

            $eventOffer->setStatus(OfferStatus::ACCEPTED);
            foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
                if ($eventOfferEntry->getEvent()->getPerson()->getId() === $eventOffer->getOfferedByPerson()->getId()) {
                    $event = $eventOfferEntry->getEvent();
                    $oldEvent = clone $event;
                    $event->setIsConfirmed(false);
                    $event->setMember($eventOffer->getOfferedToMember());
                    $event->setPerson($eventOffer->getOfferedToPerson());
                    $event->setTradeTag(TradeTag::MAYBE_TRADE);

                    $eventPast = $eventPastEvaluationService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::TRADED_TO_NEW_MEMBER);
                    $em->persist($eventPast);

                    $em->persist($event);
                } elseif ($eventOfferEntry->getEvent()->getPerson()->getId() === $ownPerson->getId()) {
                    $event = $eventOfferEntry->getEvent();
                    $oldEvent = clone $event;
                    $event->setIsConfirmed(false);
                    $event->setMember($eventOffer->getOfferedByMember());
                    $event->setPerson($eventOffer->getOfferedByPerson());
                    $event->setTradeTag(TradeTag::MAYBE_TRADE);

                    $eventPast = $eventPastEvaluationService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::TRADED_TO_NEW_MEMBER);
                    $em->persist($eventPast);

                    $em->persist($event);
                }
            }
            $eventOffer->setCloseDateTime(new \DateTime());
            $this->fastSave($eventOffer);

            $this->displaySuccess($translator->trans('messages.offer_accepted_successful', [], 'offer'));

            $receiver = $eventOffer->getOfferedByPerson()->getEmail();
            $subject = $translator->trans('emails.offer_accepted.subject', [], 'offer');
            $body = $translator->trans('emails.offer_accepted.message', [], 'offer');
            $actionText = $translator->trans('emails.offer_accepted.action_text', [], 'offer');
            $actionLink = $this->generateUrl('offer_review', ['eventOffer' => $eventOffer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $emailService->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink);
        } else {
            $this->displayError($translator->trans('messages.no_access_anymore', [], 'offer'));
        }

        return $this->redirectToRoute('offer_index');
    }

    /**
     * @Route("/{eventOffer}/reject", name="offer_reject")
     *
     * @param EventOffer $eventOffer
     *
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rejectAction(EventOffer $eventOffer, TranslatorInterface $translator, EmailService $emailService)
    {
        $ownMember = $this->getMember();
        if (null === $ownMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        $ownPerson = $this->getPerson();

        $acceptReject = $this->canAcceptOrRejectOffer($ownMember, $ownPerson, $eventOffer);
        if ($acceptReject) {
            $eventOffer->setStatus(OfferStatus::REJECTED);
            $eventOffer->setCloseDateTime(new \DateTime());
            $this->fastSave($eventOffer);
            $this->displaySuccess($translator->trans('messages.offer_rejected_successful', [], 'offer'));

            $receiver = $eventOffer->getOfferedByPerson()->getEmail();
            $subject = $translator->trans('emails.offer_rejected.subject', [], 'offer');
            $body = $translator->trans('emails.offer_rejected.message', [], 'offer');
            $actionText = $translator->trans('emails.offer_rejected.action_text', [], 'offer');
            $actionLink = $this->generateUrl('offer_review', ['eventOffer' => $eventOffer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $emailService->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink);
        } else {
            $this->displaySuccess($translator->trans('messages.no_access_anymore', [], 'offer'));
        }

        return $this->redirectToRoute('offer_index');
    }

    /**
     * @Route("/{eventOffer}/close", name="offer_close")
     *
     * @param EventOffer $eventOffer
     *
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function closeAction(EventOffer $eventOffer, TranslatorInterface $translator)
    {
        $ownMember = $this->getMember();
        if (null === $ownMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        $ownPerson = $this->getPerson();

        $canClose = $this->canCloseOffer($ownMember, $ownPerson, $eventOffer);
        if ($canClose) {
            $eventOffer->setStatus(OfferStatus::CLOSED);
            $eventOffer->setCloseDateTime(new \DateTime());
            $this->fastSave($eventOffer);
            $this->displaySuccess($translator->trans('messages.offer_close_successful', [], 'offer'));
        } else {
            $this->displaySuccess($translator->trans('messages.no_access_anymore', [], 'offer'));
        }

        return $this->redirectToRoute('offer_index');
    }

    /**
     * @Route("/{eventOffer}/remove", name="offer_remove")
     *
     * @param EventOffer $eventOffer
     *
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(EventOffer $eventOffer, TranslatorInterface $translator)
    {
        $ownMember = $this->getMember();
        if (null === $ownMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        $ownPerson = $this->getPerson();

        $canRemove = $this->canRemoveOffer($ownMember, $ownPerson, $eventOffer);
        if ($canRemove) {
            $this->fastRemove($eventOffer);
            $this->displaySuccess($translator->trans('messages.offer_remove_successful', [], 'offer'));
        } else {
            $this->displaySuccess($translator->trans('messages.no_access_anymore', [], 'offer'));
        }

        return $this->redirectToRoute('offer_index');
    }

    /**
     * @param Member     $member
     * @param Person     $person
     * @param EventOffer $eventOffer
     *
     * @return bool
     */
    private function canAcceptOrRejectOffer(Member $member, Person $person, EventOffer $eventOffer)
    {
        return $member->getId() === $eventOffer->getOfferedToMember()->getId() && $person->getId() === $eventOffer->getOfferedToPerson()->getId() && OfferStatus::OPEN === $eventOffer->getStatus();
    }

    /**
     * @param Member     $member
     * @param Person     $person
     * @param EventOffer $eventOffer
     *
     * @return bool
     */
    private function canCloseOffer(Member $member, Person $person, EventOffer $eventOffer)
    {
        return $member->getId() === $eventOffer->getOfferedByMember()->getId() && $person->getId() === $eventOffer->getOfferedByPerson()->getId() && OfferStatus::OPEN === $eventOffer->getStatus();
    }

    /**
     * @param Member     $member
     * @param Person     $person
     * @param EventOffer $eventOffer
     *
     * @return bool
     */
    private function canRemoveOffer(Member $member, Person $person, EventOffer $eventOffer)
    {
        return $member->getId() === $eventOffer->getOfferedByMember()->getId() && $person->getId() === $eventOffer->getOfferedByPerson()->getId() && OfferStatus::CREATING === $eventOffer->getStatus();
    }

    /**
     * @param EventOffer $eventOffer
     * @param bool       $remove
     *
     * @return EventOfferEntry[]
     */
    private function getInvalidEventOfferEntries(EventOffer $eventOffer, $remove = false)
    {
        $settingRepo = $this->getDoctrine()->getRepository('App:OrganisationSetting');
        $ownMember = $this->getMember();

        $em = $this->getDoctrine()->getManager();

        $organisationSettings = $settingRepo->getByOrganisation($ownMember->getOrganisation());
        $threshHold = DateTimeConverter::addDays(new \DateTime(), $organisationSettings->getTradeEventDays());

        /* @var EventOfferEntry[] $invalids */
        $invalids = [];

        foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
            if ($eventOfferEntry->getEvent()->getPerson()->getId() === $eventOffer->getOfferedByPerson()->getId() ||
                $eventOfferEntry->getEvent()->getPerson()->getId() === $eventOffer->getOfferedToPerson()->getId()) {
                if ($eventOfferEntry->getEvent()->getStartDateTime() < $threshHold) {
                    $invalids[] = $eventOfferEntry;
                    if ($remove) {
                        $em->remove($eventOfferEntry);
                    }
                }
            } else {
                $invalids[] = $eventOfferEntry;
                if ($remove) {
                    $em->remove($eventOfferEntry);
                }
            }
        }
        if ($remove) {
            $em->flush();
        }

        return $invalids;
    }
}
