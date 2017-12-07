<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventOffer;
use AppBundle\Entity\EventOfferEntry;
use AppBundle\Entity\Member;
use AppBundle\Entity\Person;
use AppBundle\Enum\EventChangeType;
use AppBundle\Enum\OfferStatus;
use AppBundle\Enum\TradeTag;
use AppBundle\Helper\DateTimeConverter;
use AppBundle\Model\Event\SearchEventModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/offer")
 * @Security("has_role('ROLE_USER')")
 */
class OfferController extends BaseFrontendController
{
    /**
     * @Route("/", name="offer_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $repo = $this->getDoctrine()->getRepository("AppBundle:EventOffer");
        $arr["author_of_offers"] = $repo->findBy(["offeredByMember" => $member->getId(), "offeredByPerson" => $this->getPerson()->getId()]);
        $arr["accepted_offers"] = $repo->findBy(["offeredToMember" => $member->getId(), "offeredToPerson" => $this->getPerson()->getId(), "status" => OfferStatus::ACCEPTED]);
        $arr["open_offers"] = $repo->findBy(["offeredToMember" => $member->getId(), "offeredToPerson" => $this->getPerson()->getId(), "status" => OfferStatus::OPEN]);
        return $this->renderWithBackUrl("offer/index.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }

    /**
     * @Route("/new", name="offer_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $memberColl = $member->getOrganisation()->getMembers();
        $memberColl->removeElement($member);
        $arr["members"] = $memberColl;
        return $this->renderWithBackUrl("offer/new.html.twig", $arr, $this->generateUrl("offer_index"));
    }

    /**
     * @Route("/start/{member}/{person}", name="offer_start")
     * @param Request $request
     * @param Member $member
     * @param Person $person
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startAction(Request $request, Member $member, Person $person)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        if ($ownMember->getOrganisation()->getId() != $member->getOrganisation()->getId() ||
            !$member->getPersons()->contains($person)) {

            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.no_access_anymore", [], "offer"));

            return $this->redirectToRoute("offer_index");
        }


        $eventOffer = new EventOffer();
        $eventOffer->setCreateDateTime(new \DateTime());
        $eventOffer->setOfferedByMember($ownMember);
        $eventOffer->setOfferedByPerson($this->getPerson());
        $eventOffer->setOfferedToMember($member);
        $eventOffer->setOfferedToPerson($person);
        $this->fastSave($eventOffer);

        return $this->redirectToRoute("offer_choose_events", ["eventOffer" => $eventOffer->getId()]);
    }

    /**
     * @Route("/{eventOffer}/choose_events", name="offer_choose_events")
     * @param Request $request
     * @param EventOffer $eventOffer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chooseEventsAction(Request $request, EventOffer $eventOffer)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        if ($ownMember->getId() != $eventOffer->getOfferedByMember()->getId() ||
            !in_array($eventOffer->getStatus(), [OfferStatus::CREATING, OfferStatus::OPEN, OfferStatus::CLOSED])) {

            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.no_access_anymore", [], "offer"));

            return $this->redirectToRoute("offer_index");
        }


        $repo = $this->getDoctrine()->getRepository("AppBundle:Organisation");
        $settingRepo = $this->getDoctrine()->getRepository("AppBundle:OrganisationSetting");
        $eventRepo = $this->getDoctrine()->getRepository("AppBundle:Event");

        if ($request->getMethod() == "POST") {
            $events = [];
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "event_") === 0) {
                    $eventId = substr($key, 6); //cut off event_
                    $event = $eventRepo->find($eventId);
                    if (
                        $event->getMember()->getId() == $eventOffer->getOfferedByMember()->getId() && $event->getPerson()->getId() == $eventOffer->getOfferedByPerson()->getId() ||
                        $event->getMember()->getId() == $eventOffer->getOfferedToMember()->getId() && $event->getPerson()->getId() == $eventOffer->getOfferedToPerson()->getId()
                    ) {
                        $events[] = $event;
                    }
                } else if ($key == "description") {
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

            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.offer_open", [], "offer"));

            $this->get("app.email_service")->sendNewOfferReceived($eventOffer);

            return $this->redirectToRoute("offer_index");
        }


        $organisationSettings = $settingRepo->getByOrganisation($ownMember->getOrganisation());
        $threshHold = DateTimeConverter::addDays(new \DateTime(), $organisationSettings->getTradeEventDays());
        $myEvents = new SearchEventModel($ownMember->getOrganisation(), $threshHold);
        $myEvents->setFilterMember($eventOffer->getOfferedByMember());
        $myEvents->setFilterPerson($eventOffer->getOfferedByPerson());
        $arr["myEventLineModels"] = $repo->findEventLineModels($myEvents);

        $theirEvents = new SearchEventModel($ownMember->getOrganisation(), $threshHold);
        $theirEvents->setFilterMember($eventOffer->getOfferedToMember());
        $theirEvents->setFilterPerson($eventOffer->getOfferedToPerson());
        $arr["theirEventLineModels"] = $repo->findEventLineModels($theirEvents);

        $arr["description_form"] = $eventOffer->getDescription();

        $offered = [];
        foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
            $offered[] = $eventOfferEntry->getEvent()->getId();
        }
        $arr["offered"] = $offered;


        $invalids = $this->getInvalidEventOfferEntries($eventOffer, false);
        if (count($invalids) > 0) {
            $translator = $this->get("translator");
            $this->displayError($translator->trans("messages.has_invalid_entries", [], "offer"));
            $arr["invalids"] = $invalids;
        }

        $arr["eventOffer"] = $eventOffer;

        return $this->renderWithBackUrl("offer/choose_events.html.twig", $arr, $this->generateUrl("offer_index"));
    }

    /**
     * @Route("/{eventOffer}/review", name="offer_review")
     * @param Request $request
     * @param EventOffer $eventOffer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reviewAction(Request $request, EventOffer $eventOffer)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }
        $ownPerson = $this->getPerson();

        $acceptReject = $this->canAcceptOrRejectOffer($ownMember, $ownPerson, $eventOffer);
        $close = $this->canCloseOffer($ownMember, $ownPerson, $eventOffer);
        if (!($acceptReject || $close)) {
            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.no_access_anymore", [], "offer"));

            return $this->redirectToRoute("offer_index");
        }

        $invalids = $this->getInvalidEventOfferEntries($eventOffer, false);
        if (count($invalids) > 0) {
            $translator = $this->get("translator");
            if ($close) {
                //can close, therefore can edit
                return $this->redirectToRoute("offer_choose_events", ["eventOffer" => $eventOffer->getId()]);
            } else {
                $eventOffer->setStatus(OfferStatus::CREATING);
                $this->fastSave($eventOffer);
                $this->displayError($translator->trans("messages.has_invalid_entries_rejected", [], "offer"));
                return $this->redirectToRoute("offer_index");
            }
        }

        $arr["acceptRejectActions"] = $acceptReject;
        $arr["closeActions"] = $close;

        if ($eventOffer->getOfferedByPerson()->getId() != $ownPerson->getId()) {
            $otherPersonId = $eventOffer->getOfferedByPerson()->getId();
        } else {
            $otherPersonId = $eventOffer->getOfferedToPerson()->getId();
        }

        /* @var Event[] $myEvents */
        $myEvents = [];
        /* @var Event[] $otherEvents */
        $otherEvents = [];

        foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
            if ($eventOfferEntry->getEvent()->getPerson()->getId() == $otherPersonId) {
                $otherEvents[] = $eventOfferEntry->getEvent();
            } else if ($eventOfferEntry->getEvent()->getPerson()->getId() == $ownPerson->getId()) {
                $myEvents[] = $eventOfferEntry->getEvent();
            }
        }

        $arr["myEvents"] = $myEvents;
        $arr["otherEvents"] = $otherEvents;
        $arr["invalids"] = $this->getInvalidEventOfferEntries($eventOffer, false);
        $arr["eventOffer"] = $eventOffer;

        return $this->renderWithBackUrl("offer/review.html.twig", $arr, $this->generateUrl("offer_index"));
    }

    /**
     * @Route("/{eventOffer}/accept", name="offer_accept")
     * @param Request $request
     * @param EventOffer $eventOffer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function acceptAction(Request $request, EventOffer $eventOffer)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $ownPerson = $this->getPerson();

        $acceptReject = $this->canAcceptOrRejectOffer($ownMember, $ownPerson, $eventOffer);
        if ($acceptReject) {
            $invalids = $this->getInvalidEventOfferEntries($eventOffer, false);
            if (count($invalids) > 0) {
                $translator = $this->get("translator");
                $eventOffer->setStatus(OfferStatus::CREATING);
                $this->fastSave($eventOffer);
                $this->displayError($translator->trans("messages.has_invalid_entries_rejected", [], "offer"));
                return $this->redirectToRoute("offer_index");
            }

            $em = $this->getDoctrine()->getManager();

            $eventOffer->setStatus(OfferStatus::ACCEPTED);
            foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
                if ($eventOfferEntry->getEvent()->getPerson()->getId() == $eventOffer->getOfferedByPerson()->getId()) {
                    $event = $eventOfferEntry->getEvent();
                    $oldEvent = clone($event);
                    $event->setIsConfirmed(false);
                    $event->setMember($eventOffer->getOfferedToMember());
                    $event->setPerson($eventOffer->getOfferedToPerson());
                    $event->setTradeTag(TradeTag::MAYBE_TRADE);

                    $myService = $this->get("app.event_past_evaluation_service");
                    $eventPast = $myService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::TRADED_TO_NEW_MEMBER);
                    $em->persist($eventPast);

                    $em->persist($event);
                } else if ($eventOfferEntry->getEvent()->getPerson()->getId() == $ownPerson->getId()) {
                    $event = $eventOfferEntry->getEvent();
                    $oldEvent = clone($event);
                    $event->setIsConfirmed(false);
                    $event->setMember($eventOffer->getOfferedByMember());
                    $event->setPerson($eventOffer->getOfferedByPerson());
                    $event->setTradeTag(TradeTag::MAYBE_TRADE);

                    $myService = $this->get("app.event_past_evaluation_service");
                    $eventPast = $myService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::TRADED_TO_NEW_MEMBER);
                    $em->persist($eventPast);

                    $em->persist($event);
                }
            }
            $eventOffer->setCloseDateTime(new \DateTime());
            $this->fastSave($eventOffer);

            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.offer_accepted_successful", [], "offer"));

            $this->get("app.email_service")->sendEventOfferAccepted($eventOffer);
        } else {
            $translator = $this->get("translator");
            $this->displayError($translator->trans("messages.no_access_anymore", [], "offer"));
        }
        return $this->redirectToRoute("offer_index");
    }

    /**
     * @Route("/{eventOffer}/reject", name="offer_reject")
     * @param Request $request
     * @param EventOffer $eventOffer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rejectAction(Request $request, EventOffer $eventOffer)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $ownPerson = $this->getPerson();

        $acceptReject = $this->canAcceptOrRejectOffer($ownMember, $ownPerson, $eventOffer);
        if ($acceptReject) {
            $eventOffer->setStatus(OfferStatus::REJECTED);
            $eventOffer->setCloseDateTime(new \DateTime());
            $this->fastSave($eventOffer);
            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.offer_rejected_successful", [], "offer"));

            $this->get("app.email_service")->sendEventOfferRejected($eventOffer);

        } else {
            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.no_access_anymore", [], "offer"));
        }
        return $this->redirectToRoute("offer_index");
    }

    /**
     * @Route("/{eventOffer}/close", name="offer_close")
     * @param Request $request
     * @param EventOffer $eventOffer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function closeAction(Request $request, EventOffer $eventOffer)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $ownPerson = $this->getPerson();

        $canClose = $this->canCloseOffer($ownMember, $ownPerson, $eventOffer);
        if ($canClose) {
            $eventOffer->setStatus(OfferStatus::CLOSED);
            $eventOffer->setCloseDateTime(new \DateTime());
            $this->fastSave($eventOffer);
            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.offer_close_successful", [], "offer"));
        } else {
            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.no_access_anymore", [], "offer"));
        }
        return $this->redirectToRoute("offer_index");
    }

    /**
     * @Route("/{eventOffer}/remove", name="offer_remove")
     * @param Request $request
     * @param EventOffer $eventOffer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request, EventOffer $eventOffer)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $ownPerson = $this->getPerson();

        $canRemove = $this->canRemoveOffer($ownMember, $ownPerson, $eventOffer);
        if ($canRemove) {
            $this->fastRemove($eventOffer);
            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.offer_remove_successful", [], "offer"));
        } else {
            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("messages.no_access_anymore", [], "offer"));
        }
        return $this->redirectToRoute("offer_index");
    }

    /**
     * @param Member $member
     * @param Person $person
     * @param EventOffer $eventOffer
     * @return bool
     */
    private function canAcceptOrRejectOffer(Member $member, Person $person, EventOffer $eventOffer)
    {
        return $member->getId() == $eventOffer->getOfferedToMember()->getId() && $person->getId() == $eventOffer->getOfferedToPerson()->getId() && $eventOffer->getStatus() == OfferStatus::OPEN;
    }


    /**
     * @param Member $member
     * @param Person $person
     * @param EventOffer $eventOffer
     * @return bool
     */
    private function canCloseOffer(Member $member, Person $person, EventOffer $eventOffer)
    {
        return $member->getId() == $eventOffer->getOfferedByMember()->getId() && $person->getId() == $eventOffer->getOfferedByPerson()->getId() && $eventOffer->getStatus() == OfferStatus::OPEN;
    }


    /**
     * @param Member $member
     * @param Person $person
     * @param EventOffer $eventOffer
     * @return bool
     */
    private function canRemoveOffer(Member $member, Person $person, EventOffer $eventOffer)
    {
        return $member->getId() == $eventOffer->getOfferedByMember()->getId() && $person->getId() == $eventOffer->getOfferedByPerson()->getId() && $eventOffer->getStatus() == OfferStatus::CREATING;
    }

    /**
     * @param EventOffer $eventOffer
     * @param $remove
     * @return EventOfferEntry[]
     */
    private function getInvalidEventOfferEntries(EventOffer $eventOffer, $remove = false)
    {
        $settingRepo = $this->getDoctrine()->getRepository("AppBundle:OrganisationSetting");
        $ownMember = $this->getMember();

        $em = $this->getDoctrine()->getManager();

        $organisationSettings = $settingRepo->getByOrganisation($ownMember->getOrganisation());
        $threshHold = DateTimeConverter::addDays(new \DateTime(), $organisationSettings->getTradeEventDays());

        /* @var EventOfferEntry[] $invalids */
        $invalids = [];

        foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
            if ($eventOfferEntry->getEvent()->getPerson()->getId() == $eventOffer->getOfferedByPerson()->getId() ||
                $eventOfferEntry->getEvent()->getPerson()->getId() == $eventOffer->getOfferedToPerson()->getId()) {
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