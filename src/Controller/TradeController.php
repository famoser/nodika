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
use App\Entity\Event;
use App\Entity\EventOffer;
use App\Entity\EventOfferAuthorization;
use App\Entity\EventOfferEntry;
use App\Entity\FrontendUser;
use App\Entity\Setting;
use App\Enum\OfferStatus;
use App\Enum\SignatureStatus;
use App\Model\Event\SearchModel;
use App\Service\EmailService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/trade")
 * @Security("has_role('ROLE_USER')")
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
        $repo = $this->getDoctrine()->getRepository('App:EventOffer');
        return $this->render('trade/index.html.twig');
    }

    /**
     * @Route("/api/my_events", name="trade_my_events")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @throws \Exception
     */
    public function apiMyEventsAction(SerializerInterface $serializer)
    {
        //get all tradeable events
        $searchModel = new SearchModel(SearchModel::YEAR);
        $searchModel->setMembers($this->getUser()->getMembers());
        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchModel);

        $apiEvents = [];
        foreach ($events as $event) {
            if ($event->getFrontendUser() == null || $event->getFrontendUser()->getId() == $this->getUser()->getId()) {
                $apiEvents[] = $event;
            }
        }

        return new JsonResponse($serializer->serialize($apiEvents, "json", ["attributes" => ["id", "startDateTime", "endDateTime", "member" => ["name"], "frontendUser" => ["id", "fullName"]]]), 200, [], true);
    }

    /**
     * @Route("/api/their_events", name="trade_their_events")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @throws \Exception
     */
    public function apiTheirEventsAction(SerializerInterface $serializer)
    {
        //get all tradeable events
        $searchModel = new SearchModel(SearchModel::YEAR);
        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchModel);

        $apiEvents = [];
        foreach ($events as $event) {
            if (!$this->getUser()->getMembers()->contains($event->getMember())) {
                $apiEvents[] = $event;
            }
        }

        return new JsonResponse($serializer->serialize($apiEvents, "json", ["attributes" => ["id", "startDateTime", "endDateTime", "member" => ["name"], "frontendUser" => ["id", "fullName"]]]), 200, [], true);
    }

    /**
     * @Route("/api/check_possible_receivers", name="trade_possible_receivers")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @throws \Exception
     */
    public function apiPossibleReceivers(Request $request, SerializerInterface $serializer)
    {
        if (!$request->request->has("their_event_ids")) {
            return new JsonResponse([]);
        }
        $eventIds = $request->request->get("their_event_ids");
        $events = $this->getEventsFromIds($eventIds);
        if ($events !== false) {
            return new JsonResponse($serializer->serialize($this->getPossibleReceivers($events), "json", ["attributes" => ["id", "fullName"]]), 200, true);
        } else {
            return new JsonResponse([]);
        }
    }

    /**
     * @param int[] $eventIds
     * @return Event[]|bool
     */
    private function getEventsFromIds($eventIds)
    {
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        /* @var \App\Entity\Event[] $events */
        $events = [];
        foreach ($eventIds as $eventId) {
            $events[] = $eventRepo->find($eventId);
        }

        if (in_array(null, $events)) {
            return false;
        }

        return $events;
    }

    /**
     * @param Event[] $events
     * @return array
     */
    private function getPossibleReceivers($events)
    {
        if (count($events) == 0) {
            return [];
        }

        //events must be from same member, and at least one user must be able to authorize the trade
        $member = $events[0]->getMember();
        $frontendUser = $events[0]->getFrontendUser();
        foreach ($events as $event) {
            if ($member !== $event->getMember() || ($event->getFrontendUser() !== null && $frontendUser !== $event->getFrontendUser())) {
                return [];
            }

            if ($event->getFrontendUser() !== null) {
                $frontendUser = $event->getFrontendUser();
            }
        }

        if ($frontendUser != null) {
            return [$frontendUser];
        } else {
            return $member->getFrontendUsers()->toArray();
        }
    }

    /**
     * @Route("/api/create", name="trade_possible_receivers")
     *
     * @param Request $request
     * @param EmailService $emailService
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function apiCreate(Request $request, EmailService $emailService, TranslatorInterface $translator)
    {
        if (!$request->request->has("my_event_ids") || !$request->request->has("their_event_ids") || !$request->request->has("target_user")) {
            return new Response("NACK");
        }

        //check events can be traded with chosen target
        $theirEvents = $this->getEventsFromIds($request->request->get("their_event_ids"));
        $possibleReceivers = $this->getPossibleReceivers($theirEvents);
        $targetUser = $this->getDoctrine()->getRepository(FrontendUser::class)->find($request->request->get("target_user"));
        if (!in_array($targetUser, $possibleReceivers)) {
            return new Response("NACK");
        }

        //check source can indeed trade all these events
        $myEvents = $this->getEventsFromIds($request->request->get("my_event_ids"));
        $possibleReceivers = $this->getPossibleReceivers($myEvents);
        if (!in_array($this->getUser(), $possibleReceivers)) {
            return new Response("NACK");
        }

        //construct the offer
        $eventOffer = new EventOffer();
        $eventOffer->setStatus(OfferStatus::OPEN);
        if ($request->request->has("description")) {
            $eventOffer->setMessage($request->request->get("description"));
        }

        //my events which are new the events of the the receiver of the trade offer
        $eventOfferAuthorization = new EventOfferAuthorization();
        $eventOfferAuthorization->setEventOffer($eventOffer);
        $eventOfferAuthorization->setSignatureStatus(SignatureStatus::PENDING);
        $eventOfferAuthorization->setSignedBy($targetUser);
        foreach ($myEvents as $myEvent) {
            $eventOfferEntry = new EventOfferEntry();
            $eventOfferEntry->setEvent($myEvent);
            $eventOfferEntry->setEventOffer($eventOffer);
            $eventOfferEntry->setTargetFrontendUser($targetUser);
            $eventOfferEntry->setTargetMember($targetUser->getMembers()->first());
            $eventOfferEntry->setEventOfferAuthorization($eventOfferAuthorization);
        }

        //their events which are new the events of the creator of the trade offer
        $eventOfferAuthorization = new EventOfferAuthorization();
        $eventOfferAuthorization->setEventOffer($eventOffer);
        $eventOfferAuthorization->setSignatureStatus(SignatureStatus::SIGNED);
        $eventOfferAuthorization->setSignedBy($this->getUser());
        foreach ($theirEvents as $theirEvent) {
            $eventOfferEntry = new EventOfferEntry();
            $eventOfferEntry->setEvent($theirEvent);
            $eventOfferEntry->setEventOffer($eventOffer);
            $eventOfferEntry->setTargetFrontendUser($this->getUser());
            $eventOfferEntry->setTargetMember($this->getUser()->getMembers()->first());
            $eventOfferEntry->setEventOfferAuthorization($eventOfferAuthorization);
        }

        $emailService->sendActionEmail(
            $targetUser->getEmail(),
            $translator->trans("emails.new_offer.subject", [], "trade"),
            $translator->trans("emails.new_offer.message", [], "trade"),
            $translator->trans("emails.new_offer.action_text", [], "trade"),
            $this->generateUrl("trade_index"));

        return new Response("ACK");
    }
}
