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
use App\Entity\Member;
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

        return new JsonResponse($serializer->serialize($apiEvents, "json", ["attributes" => ["id", "startDateTime", "endDateTime", "member" => ["id", "name"], "frontendUser" => ["id", "fullName"]]]), 200, [], true);
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

        return new JsonResponse($serializer->serialize($apiEvents, "json", ["attributes" => ["id", "startDateTime", "endDateTime", "member" => ["id", "name"], "frontendUser" => ["id", "fullName"]]]), 200, [], true);
    }

    /**
     * @Route("/api/users", name="trade_users")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function apiUsers(SerializerInterface $serializer)
    {
        $members = $this->getDoctrine()->getRepository(FrontendUser::class)->findBy(["deletedAt" => null], ["familyName" => "ASC", "givenName" => "ASC"]);
        return new JsonResponse($serializer->serialize($members, "json", ["attributes" => ["id", "fullName", "members" => ["id", "name"]]]), 200, [], true);
    }

    /**
     * @Route("/api/members/{frontendUser}", name="trade_members")
     *
     * @param SerializerInterface $serializer
     * @param FrontendUser $frontendUser
     * @return JsonResponse
     */
    public function apiMembers(SerializerInterface $serializer, FrontendUser $frontendUser)
    {
        return new JsonResponse($serializer->serialize($frontendUser->getActiveMembers(), "json", ["attributes" => ["id", "name"]]), 200, [], true);
    }

    /**
     * @Route("/api/user", name="trade_user")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function apiUser(SerializerInterface $serializer)
    {
        return new JsonResponse($serializer->serialize($this->getUser(), "json", ["attributes" => ["id", "fullName", "members" => ["id", "name"]]]), 200, [], true);
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
     * contructs the event offer, returns false if any values are wrong
     *
     * @param $values
     * @return EventOffer|bool
     */
    private function constructEventOffer($values)
    {
        //check POST parameters
        $required = ["my_event_ids", "their_event_ids", "target_member_id", "target_user_id", "source_member_id"];
        foreach ($required as $item) {
            if (!isset($values[$item])) {
                return false;
            }
        }
        if (count($values) > count($required)) {
            return false;
        }

        //check events can be traded with chosen target
        $theirEvents = $this->getEventsFromIds($values["their_event_ids"]);
        if (!$theirEvents) {
            return false;
        }

        //get targets
        $targetMember = $this->getDoctrine()->getRepository(Member::class)->find((int)$values["target_member_id"]);
        $targetFrontendUser = $this->getDoctrine()->getRepository(FrontendUser::class)->find((int)$values["target_user_id"]);

        //ensure both are set now
        if ($targetFrontendUser == null || $targetMember == null) {
            return false;
        }

        //check both are alive & connected
        if (!$targetFrontendUser->getMembers()->contains($targetMember) || $targetMember->isDeleted() || $targetFrontendUser->isDeleted()) {
            return false;
        }

        //events must be from same member, and at least one user must be able to authorize the trade
        foreach ($theirEvents as $event) {

            //ensure member matches
            if ($targetMember !== $event->getMember()) {
                return false;
            }

            //ensure only single frontend user part of trade
            if ($event->getFrontendUser() !== null && $event->getFrontendUser() !== $targetFrontendUser) {
                return false;
            }
        }


        //check source can indeed trade all these events
        $myEvents = $this->getEventsFromIds($values["my_event_ids"]);
        if (!$myEvents) {
            return false;
        }

        //get source
        $sourceMember = $this->getDoctrine()->getRepository(Member::class)->find((int)$values["source_member_id"]);
        $sourceUser = $this->getUser();

        //check both are alive & connected
        if (!$this->getUser()->getMembers()->contains($sourceMember) || $sourceMember->isDeleted() || $sourceUser->isDeleted()) {
            return false;
        }

        //check events belong to user
        foreach ($myEvents as $myEvent) {
            if ($myEvent->getMember() !== $sourceMember) {
                return false;
            }

            if ($myEvent->getFrontendUser() != null && $myEvent->getFrontendUser() !== $sourceUser) {
                return false;
            }
        }

        //construct the offer
        $eventOffer = new EventOffer();
        $eventOffer->setStatus(OfferStatus::OPEN);
        if (isset($values["description"])) {
            $eventOffer->setMessage($values["description"]);
        }

        //my events which are new the events of the the receiver of the trade offer
        $eventOfferAuthorization = new EventOfferAuthorization();
        $eventOfferAuthorization->setEventOffer($eventOffer);
        $eventOfferAuthorization->setSignatureStatus(SignatureStatus::PENDING);
        $eventOfferAuthorization->setSignedBy($targetFrontendUser);
        foreach ($myEvents as $myEvent) {
            $eventOfferEntry = new EventOfferEntry();
            $eventOfferEntry->setEvent($myEvent);
            $eventOfferEntry->setEventOffer($eventOffer);
            $eventOfferEntry->setTargetMember($targetMember);
            $eventOfferEntry->setTargetFrontendUser($targetFrontendUser);
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
            $eventOfferEntry->setTargetFrontendUser($sourceUser);
            $eventOfferEntry->setTargetMember($sourceMember);
            $eventOfferEntry->setEventOfferAuthorization($eventOfferAuthorization);
        }

        return $eventOffer;
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
        //try to contruct offer from POST values
        $eventOffer = $this->constructEventOffer($request->request->all());
        if (!$eventOffer)
            return new Response("NACK");

        //send out all authorization request emails
        foreach ($eventOffer->getAuthorizations() as $authorization) {
            if ($authorization->getSignatureStatus() == SignatureStatus::PENDING) {
                $emailService->sendActionEmail(
                    $authorization->getSignedBy()->getEmail(),
                    $translator->trans("emails.new_offer.subject", [], "trade"),
                    $translator->trans("emails.new_offer.message", [], "trade"),
                    $translator->trans("emails.new_offer.action_text", [], "trade"),
                    $this->generateUrl("trade_index"));
            }
        }
        return new Response("ACK");
    }
}
