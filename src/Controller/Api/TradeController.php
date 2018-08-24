<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Controller\Api\Base\BaseApiController;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\EventOffer;
use App\Entity\EventOfferAuthorization;
use App\Entity\EventOfferEntry;
use App\Enum\OfferStatus;
use App\Enum\SignatureStatus;
use App\Model\Event\SearchModel;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/trade")
 */
class TradeController extends BaseApiController
{
    /**
     * @Route("/my_events", name="api_trade_my_events")
     *
     * @param SerializerInterface $serializer
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function apiMyEventsAction(SerializerInterface $serializer)
    {
        //get all tradeable events
        $searchModel = new SearchModel(SearchModel::YEAR);
        $searchModel->setClinics($this->getUser()->getClinics());
        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchModel);

        $apiEvents = [];
        foreach ($events as $event) {
            if (null === $event->getDoctor() || $event->getDoctor()->getId() === $this->getUser()->getId()) {
                $apiEvents[] = $event;
            }
        }

        return new JsonResponse($serializer->serialize($apiEvents, 'json', ['attributes' => ['id', 'startDateTime', 'endDateTime', 'clinic' => ['id', 'name'], 'doctor' => ['id', 'fullName']]]), 200, [], true);
    }

    /**
     * @Route("/their_events", name="ap_trade_their_events")
     *
     * @param SerializerInterface $serializer
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function theirEventsAction(SerializerInterface $serializer)
    {
        //get all tradeable events
        $searchModel = new SearchModel(SearchModel::YEAR);
        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchModel);

        //exclude own events
        $apiEvents = [];
        foreach ($events as $event) {
            if (!$this->getUser()->getClinics()->contains($event->getClinic())) {
                $apiEvents[] = $event;
            }
        }

        return new JsonResponse($serializer->serialize($apiEvents, 'json', ['attributes' => ['id', 'startDateTime', 'endDateTime', 'clinic' => ['id', 'name'], 'doctor' => ['id', 'fullName']]]), 200, [], true);
    }

    /**
     * @Route("/clinics", name="api_trade_clinics")
     *
     * @param SerializerInterface $serializer
     * @param Doctor              $doctor
     *
     * @return JsonResponse
     */
    public function apiClinics(SerializerInterface $serializer)
    {
        $clinics = $this->getDoctrine()->getRepository(Clinic::class)->findBy(['deletedAt' => null], ['name' => 'ASC']);

        return new JsonResponse($serializer->serialize($clinics, 'json', ['attributes' => ['id', 'name', 'doctors' => ['id', 'fullName']]]), 200, [], true);
    }

    /**
     * @Route("/self", name="api_trade_self")
     *
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    public function self(SerializerInterface $serializer)
    {
        return new JsonResponse($serializer->serialize($this->getUser(), 'json', ['attributes' => ['id', 'fullName', 'clinics' => ['id', 'name']]]), 200, [], true);
    }

    /**
     * @param int[] $eventIds
     *
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

        if (in_array(null, $events, true)) {
            return false;
        }

        return $events;
    }

    /**
     * constructs the event offer, returns false if any values are wrong.
     *
     * @param $values
     *
     * @return EventOffer|bool
     */
    private function constructEventOffer($values)
    {
        //check POST parameters
        $required = ['sender_event_ids', 'receiver_event_ids', 'sender_clinic_id', 'receiver_doctor_id', 'receiver_clinic_id', 'description'];
        foreach ($required as $item) {
            if (!isset($values[$item])) {
                return false;
            }
        }
        if (count($values) > count($required)) {
            return false;
        }

        //check events can be traded with chosen target
        $theirEvents = $this->getEventsFromIds($values['receiver_event_ids']);
        if (!$theirEvents) {
            return false;
        }

        //get targets
        $targetClinic = $this->getDoctrine()->getRepository(Clinic::class)->find((int) $values['receiver_clinic_id']);
        $targetDoctor = $this->getDoctrine()->getRepository(Doctor::class)->find((int) $values['receiver_doctor_id']);

        //ensure both are set now
        if (null === $targetDoctor || null === $targetClinic) {
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
            if (null !== $event->getDoctor() && $event->getDoctor() !== $targetDoctor) {
                return false;
            }
        }

        //check source can indeed trade all these events
        $myEvents = $this->getEventsFromIds($values['sender_event_ids']);
        if (!$myEvents) {
            return false;
        }

        //get source
        $sourceClinic = $this->getDoctrine()->getRepository(Clinic::class)->find((int) $values['sender_clinic_id']);
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

            if (null !== $myEvent->getDoctor() && $myEvent->getDoctor() !== $sourceUser) {
                return false;
            }
        }

        //construct the offer
        $eventOffer = new EventOffer();
        $eventOffer->setStatus(OfferStatus::OPEN);
        $eventOffer->setMessage($values['description']);

        //my events which are new the events of the the receiver of the trade offer
        $eventOfferAuthorization = new EventOfferAuthorization();
        $eventOfferAuthorization->setEventOffer($eventOffer);
        $eventOfferAuthorization->setSignatureStatus(SignatureStatus::PENDING);
        $eventOfferAuthorization->setSignedBy($targetDoctor);
        $eventOfferAuthorization->setEventOffer($eventOffer);
        $eventOffer->getAuthorizations()->add($eventOfferAuthorization);

        //add concrete events
        foreach ($myEvents as $myEvent) {
            $eventOfferEntry = new EventOfferEntry();
            $eventOfferEntry->setEvent($myEvent);
            $eventOfferEntry->setEventOffer($eventOffer);
            $eventOfferEntry->setTargetClinic($targetClinic);
            $eventOfferEntry->setTargetDoctor($targetDoctor);
            $eventOfferEntry->setEventOfferAuthorization($eventOfferAuthorization);
            $eventOffer->getEntries()->add($eventOfferEntry);
            $eventOfferAuthorization->getAuthorizes()->add($eventOfferEntry);
        }

        //their events which are new the events of the creator of the trade offer
        $eventOfferAuthorization = new EventOfferAuthorization();
        $eventOfferAuthorization->setEventOffer($eventOffer);
        $eventOfferAuthorization->setSignatureStatus(SignatureStatus::SIGNED);
        $eventOfferAuthorization->setSignedBy($this->getUser());
        $eventOffer->getAuthorizations()->add($eventOfferAuthorization);

        //add concrete events
        foreach ($theirEvents as $theirEvent) {
            $eventOfferEntry = new EventOfferEntry();
            $eventOfferEntry->setEvent($theirEvent);
            $eventOfferEntry->setEventOffer($eventOffer);
            $eventOfferEntry->setTargetDoctor($sourceUser);
            $eventOfferEntry->setTargetClinic($sourceClinic);
            $eventOfferEntry->setEventOfferAuthorization($eventOfferAuthorization);
            $eventOffer->getEntries()->add($eventOfferEntry);
            $eventOfferAuthorization->getAuthorizes()->add($eventOfferEntry);
        }

        $this->fastSave($eventOffer);

        return $eventOffer;
    }

    /**
     * @Route("/create", name="api_trade_create")
     *
     * @param Request             $request
     * @param EmailService        $emailService
     * @param TranslatorInterface $translator
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return Response
     */
    public function create(Request $request, EmailService $emailService, TranslatorInterface $translator)
    {
        //try to construct offer from POST values
        $eventOffer = $this->constructEventOffer(json_decode($request->getContent(), true));
        if (!$eventOffer) {
            $this->displayError($translator->trans('index.danger.trade_offer_invalid', [], 'trade'));

            return new Response('NACK');
        }

        //send out all authorization request emails
        foreach ($eventOffer->getAuthorizations() as $authorization) {
            if (SignatureStatus::PENDING === $authorization->getSignatureStatus()) {
                $emailService->sendActionEmail(
                    $authorization->getSignedBy()->getEmail(),
                    $translator->trans('emails.new_offer.subject', [], 'trade'),
                    $translator->trans('emails.new_offer.message', [], 'trade'),
                    $translator->trans('emails.new_offer.action_text', [], 'trade'),
                    $this->generateUrl('index_index'));
            }
        }

        $this->displaySuccess($translator->trans('index.success.created_trade_offer', [], 'trade'));

        return new Response('ACK');
    }
}
