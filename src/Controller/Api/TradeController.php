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
use App\Enum\AuthorizationStatus;
use App\Model\Event\SearchModel;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/trade")
 */
class TradeController extends BaseApiController
{
    /**
     * @Route("/my_events", name="api_trade_my_events")
     *
     * @return JsonResponse
     */
    public function apiMyEventsAction()
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

        return $this->returnEvents($apiEvents);
    }

    /**
     * @Route("/their_events", name="ap_trade_their_events")
     *
     * @return JsonResponse
     */
    public function theirEventsAction()
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

        return $this->returnEvents($apiEvents);
    }

    /**
     * @Route("/clinics", name="api_trade_clinics")
     *
     * @return JsonResponse
     */
    public function apiClinics()
    {
        $clinics = $this->getDoctrine()->getRepository(Clinic::class)->findBy(['deletedAt' => null], ['name' => 'ASC']);

        return $this->returnClinics($clinics);
    }

    /**
     * @Route("/self", name="api_trade_self")
     *
     * @return JsonResponse
     */
    public function self()
    {
        return $this->returnDoctors($this->getUser());
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

        if (\in_array(null, $events, true)) {
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
        if (\count($values) > \count($required)) {
            return false;
        }

        //get receiver stuff
        $receiverEventIds = $this->getEventsFromIds($values['receiver_event_ids']);
        $receiverEvents = $this->getDoctrine()->getRepository(Event::class)->findBy(['id' => array_values($receiverEventIds)]);
        $receiverClinic = $this->getDoctrine()->getRepository(Clinic::class)->find((int) $values['receiver_clinic_id']);
        $receiverDoctor = $this->getDoctrine()->getRepository(Doctor::class)->find((int) $values['receiver_doctor_id']);

        //get sender stuff
        $senderEventIds = $this->getEventsFromIds($values['sender_event_ids']);
        $senderEvents = $this->getDoctrine()->getRepository(Event::class)->findBy(['id' => array_values($senderEventIds)]);
        $senderClinic = $this->getDoctrine()->getRepository(Clinic::class)->find((int) $values['sender_clinic_id']);
        $senderDoctor = $this->getUser();

        //construct the offer
        $eventOffer = new EventOffer();
        $eventOffer->setMessage($values['description']);

        $eventOffer->setSender($senderDoctor);
        $eventOffer->setSenderClinic($senderClinic);
        foreach ($senderEvents as $senderEvent) {
            $eventOffer->getEventsWhichChangeOwner()->add($senderEvent);
        }

        $eventOffer->setReceiver($receiverDoctor);
        $eventOffer->setReceiverClinic($receiverClinic);
        foreach ($receiverEvents as $receiverEvent) {
            $eventOffer->getEventsWhichChangeOwner()->add($receiverEvent);
        }

        //save if offer is valid
        if ($eventOffer->isValid()) {
            $this->fastSave($eventOffer);

            return $eventOffer;
        }

        return false;
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
            if (AuthorizationStatus::PENDING === $authorization->getReceiverAuthorizationStatus()) {
                $emailService->sendActionEmail(
                    $authorization->getReceiverSignature()->getEmail(),
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
