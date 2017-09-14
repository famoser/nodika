<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Administration\Organisation\MemberController;
use AppBundle\Controller\Base\BaseController;
use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\EventOffer;
use AppBundle\Entity\EventOfferEntry;
use AppBundle\Entity\Member;
use AppBundle\Entity\Person;
use AppBundle\Enum\OfferStatus;
use AppBundle\Helper\DateTimeConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
            $this->redirectToRoute("dashboard_index");
        }

        $repo = $this->getDoctrine()->getRepository("AppBundle:EventOffer");
        $arr["author_of_offers"] = $repo->findBy(["offeredByMember" => $member->getId(), "offeredByPerson" => $this->getPerson()->getId()]);
        $arr["accepted_offers"] = $repo->findBy(["offeredToMember" => $member->getId(), "offeredToPerson" => $this->getPerson()->getId(), "status" => OfferStatus::OFFER_ACCEPTED]);
        $arr["open_offers"] = $repo->findBy(["offeredToMember" => $member->getId(), "offeredToPerson" => $this->getPerson()->getId(), "status" => OfferStatus::OFFER_OPEN]);
        return $this->render("dashboard/index.html.twig", $arr);
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
            $this->redirectToRoute("dashboard_index");
        }

        $repo = $this->getDoctrine()->getRepository("AppBundle:EventOffer");
        $arr["author_of_offers"] = $repo->findBy(["offeredByMember" => $member->getId(), "offeredByPerson" => $this->getPerson()->getId()]);
        $arr["accepted_offers"] = $repo->findBy(["offeredToMember" => $member->getId(), "offeredToPerson" => $this->getPerson()->getId(), "status" => OfferStatus::OFFER_ACCEPTED]);
        $arr["open_offers"] = $repo->findBy(["offeredToMember" => $member->getId(), "offeredToPerson" => $this->getPerson()->getId(), "status" => OfferStatus::OFFER_OPEN]);
        return $this->render("dashboard/index.html.twig", $arr);
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
            $this->redirectToRoute("dashboard_index");
        }

        if ($ownMember->getOrganisation()->getId() != $member->getOrganisation()->getId() ||
            !$member->getPersons()->contains($person)) {
            $this->redirectToRoute("offer_index");
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
     * @Route("/choose_events/{eventOffer}", name="offer_choose_events")
     * @param Request $request
     * @param EventOffer $eventOffer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chooseEventsAction(Request $request, EventOffer $eventOffer)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            $this->redirectToRoute("dashboard_index");
        }

        if ($ownMember->getId() != $eventOffer->getOfferedByMember()->getId() ||
            !in_array($eventOffer->getStatus(), [OfferStatus::OFFER_CREATING, OfferStatus::OFFER_OPEN, OfferStatus::OFFER_CLOSED])) {
            $this->redirectToRoute("offer_index");
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
                        $event->getMember()->getId() == $eventOffer->getOfferedByMember() && $event->getPerson()->getId() == $eventOffer->getOfferedByPerson() ||
                        $event->getMember()->getId() == $eventOffer->getOfferedToMember() && $event->getPerson()->getId() == $eventOffer->getOfferedToPerson()
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
            $eventOffer->setStatus(OfferStatus::OFFER_OPEN);
            $em->flush();
            return $this->redirectToRoute("offer_index");
        }


        $organisationSettings = $settingRepo->getByOrganisation($ownMember->getOrganisation());

        $threshHold = DateTimeConverter::addDays(new \DateTime(), $organisationSettings->getTradeEventDays());
        $arr["myEventLineModels"] = $repo->findEventLineModels($ownMember->getOrganisation(), $threshHold, $eventOffer->getOfferedByMember(), $eventOffer->getOfferedByPerson());
        $arr["theirEventLineModels"] = $repo->findEventLineModels($ownMember->getOrganisation(), $threshHold, $eventOffer->getOfferedToMember(), $eventOffer->getOfferedToPerson());
        $arr["description"] = $eventOffer->getDescription();

        $offered = [];
        foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
            $offered[] = $eventOfferEntry->getEvent()->getId();
        }
        $arr["offered"] = $offered;

        return $this->render("dashboard/index.html.twig", $arr);
    }


    /**
     * @Route("/review_offer/{eventOffer}", name="offer_review_offer")
     * @param Request $request
     * @param EventOffer $eventOffer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reviewOfferAction(Request $request, EventOffer $eventOffer)
    {
        $ownMember = $this->getMember();
        if ($ownMember == null) {
            $this->redirectToRoute("dashboard_index");
        }

        if ($ownMember->getId() != $eventOffer->getOfferedByMember()->getId() ||
            !in_array($eventOffer->getStatus(), [OfferStatus::OFFER_CREATING, OfferStatus::OFFER_OPEN, OfferStatus::OFFER_CLOSED])) {
            $this->redirectToRoute("offer_index");
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
                        $event->getMember()->getId() == $eventOffer->getOfferedByMember() && $event->getPerson()->getId() == $eventOffer->getOfferedByPerson() ||
                        $event->getMember()->getId() == $eventOffer->getOfferedToMember() && $event->getPerson()->getId() == $eventOffer->getOfferedToPerson()
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
            $eventOffer->setStatus(OfferStatus::OFFER_OPEN);
            $em->flush();
            return $this->redirectToRoute("offer_index");
        }


        $organisationSettings = $settingRepo->getByOrganisation($ownMember->getOrganisation());

        $threshHold = DateTimeConverter::addDays(new \DateTime(), $organisationSettings->getTradeEventDays());
        $arr["myEventLineModels"] = $repo->findEventLineModels($ownMember->getOrganisation(), $threshHold, $eventOffer->getOfferedByMember(), $eventOffer->getOfferedByPerson());
        $arr["theirEventLineModels"] = $repo->findEventLineModels($ownMember->getOrganisation(), $threshHold, $eventOffer->getOfferedToMember(), $eventOffer->getOfferedToPerson());
        $arr["description"] = $eventOffer->getDescription();

        $offered = [];
        foreach ($eventOffer->getEventOfferEntries() as $eventOfferEntry) {
            $offered[] = $eventOfferEntry->getEvent()->getId();
        }
        $arr["offered"] = $offered;

        return $this->render("dashboard/index.html.twig", $arr);
    }
}