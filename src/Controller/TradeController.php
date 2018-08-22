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
use App\Entity\Doctor;
use App\Entity\Clinic;
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
        $searchModel->setClinics($this->getUser()->getClinics());
        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchModel);

        $apiEvents = [];
        foreach ($events as $event) {
            if ($event->getDoctor() == null || $event->getDoctor()->getId() == $this->getUser()->getId()) {
                $apiEvents[] = $event;
            }
        }

        return new JsonResponse($serializer->serialize($apiEvents, "json", ["attributes" => ["id", "startDateTime", "endDateTime", "clinic" => ["id", "name"], "doctor" => ["id", "fullName"]]]), 200, [], true);
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
            if (!$this->getUser()->getClinics()->contains($event->getClinic())) {
                $apiEvents[] = $event;
            }
        }

        return new JsonResponse($serializer->serialize($apiEvents, "json", ["attributes" => ["id", "startDateTime", "endDateTime", "clinic" => ["id", "name"], "doctor" => ["id", "fullName"]]]), 200, [], true);
    }

    /**
     * @Route("/api/users", name="trade_users")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function apiUsers(SerializerInterface $serializer)
    {
        $clinics = $this->getDoctrine()->getRepository(Doctor::class)->findBy(["deletedAt" => null], ["familyName" => "ASC", "givenName" => "ASC"]);
        return new JsonResponse($serializer->serialize($clinics, "json", ["attributes" => ["id", "fullName", "clinics" => ["id", "name"]]]), 200, [], true);
    }

    /**
     * @Route("/api/clinics/{doctor}", name="trade_clinics")
     *
     * @param SerializerInterface $serializer
     * @param Doctor $doctor
     * @return JsonResponse
     */
    public function apiClinics(SerializerInterface $serializer, Doctor $doctor)
    {
        $activeClinics = [];
        foreach ($doctor->getClinics() as $clinic) {
            if (!$clinic->isDeleted()) {
                $activeClinics[] = $clinic;
            }
        }

        return new JsonResponse($serializer->serialize($activeClinics, "json", ["attributes" => ["id", "name"]]), 200, [], true);
    }

    /**
     * @Route("/api/user", name="trade_user")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function apiUser(SerializerInterface $serializer)
    {
        return new JsonResponse($serializer->serialize($this->getUser(), "json", ["attributes" => ["id", "fullName", "clinics" => ["id", "name"]]]), 200, [], true);
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
        $required = ["my_event_ids", "their_event_ids", "target_clinic_id", "target_user_id", "source_clinic_id"];
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
        $targetClinic = $this->getDoctrine()->getRepository(Clinic::class)->find((int)$values["target_clinic_id"]);
        $targetDoctor = $this->getDoctrine()->getRepository(Doctor::class)->find((int)$values["target_user_id"]);

        //ensure both are set now
        if ($targetDoctor == null || $targetClinic == null) {
            return false;
        }

        //check both are alive & connected
        if (!$targetDoctor->getClinics()->contains($targetClinic) || $targetClinic->isDeleted() || $targetDoctor->isDeleted()) {
            return false;
        }

        //events must be from same clinic, and at least one user must be able to authorize the trade
        foreach ($theirEvents as $event) {

            //ensure clinic matches
            if ($targetClinic !== $event->getClinic()) {
                return false;
            }

            //ensure only single frontend user part of trade
            if ($event->getDoctor() !== null && $event->getDoctor() !== $targetDoctor) {
                return false;
            }
        }


        //check source can indeed trade all these events
        $myEvents = $this->getEventsFromIds($values["my_event_ids"]);
        if (!$myEvents) {
            return false;
        }

        //get source
        $sourceClinic = $this->getDoctrine()->getRepository(Clinic::class)->find((int)$values["source_clinic_id"]);
        $sourceUser = $this->getUser();

        //check both are alive & connected
        if (!$this->getUser()->getClinics()->contains($sourceClinic) || $sourceClinic->isDeleted() || $sourceUser->isDeleted()) {
            return false;
        }

        //check events belong to user
        foreach ($myEvents as $myEvent) {
            if ($myEvent->getClinic() !== $sourceClinic) {
                return false;
            }

            if ($myEvent->getDoctor() != null && $myEvent->getDoctor() !== $sourceUser) {
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
        $eventOfferAuthorization->setSignedBy($targetDoctor);
        foreach ($myEvents as $myEvent) {
            $eventOfferEntry = new EventOfferEntry();
            $eventOfferEntry->setEvent($myEvent);
            $eventOfferEntry->setEventOffer($eventOffer);
            $eventOfferEntry->setTargetClinic($targetClinic);
            $eventOfferEntry->setTargetDoctor($targetDoctor);
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
            $eventOfferEntry->setTargetDoctor($sourceUser);
            $eventOfferEntry->setTargetClinic($sourceClinic);
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
