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
use App\Helper\DoctrineHelper;
use App\Model\Event\SearchModel;
use App\Service\EmailService;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/trade')]
class TradeController extends BaseApiController
{
    #[Route(path: '/my_events', name: 'api_trade_my_events')]
    public function apiMyEvents(ManagerRegistry $registry, SerializerInterface $serializer): JsonResponse
    {
        // get all tradeable events
        $searchModel = new SearchModel(SearchModel::YEAR);
        $searchModel->setClinics($this->getUser()->getClinics());
        $events = $registry->getRepository(Event::class)->search($searchModel);

        $apiEvents = [];
        foreach ($events as $event) {
            if (null === $event->getDoctor() || $event->getDoctor()->getId() === $this->getUser()->getId()) {
                $apiEvents[] = $event;
            }
        }

        return $this->returnEvents($serializer, $apiEvents);
    }

    #[Route(path: '/their_events', name: 'ap_trade_their_events')]
    public function theirEvents(ManagerRegistry $registry, SerializerInterface $serializer): JsonResponse
    {
        // get all tradeable events
        $searchModel = new SearchModel(SearchModel::YEAR);
        $events = $registry->getRepository(Event::class)->search($searchModel);

        // exclude own events
        $apiEvents = [];
        foreach ($events as $event) {
            if (!$this->getUser()->getClinics()->contains($event->getClinic())) {
                $apiEvents[] = $event;
            }
        }

        return $this->returnEvents($serializer, $apiEvents);
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/clinics', name: 'api_trade_clinics')]
    public function apiClinics(ManagerRegistry $registry, SerializerInterface $serializer)
    {
        $clinics = $registry->getRepository(Clinic::class)->findBy(['deletedAt' => null], ['name' => 'ASC']);

        return $this->returnClinics($serializer, $clinics);
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/self', name: 'api_trade_self')]
    public function self(SerializerInterface $serializer)
    {
        return $this->returnDoctors($serializer, $this->getUser());
    }

    /**
     * @param int[] $eventIds
     *
     * @return Event[]|bool
     */
    private function getEventsFromIds(ManagerRegistry $registry, $eventIds): false|array
    {
        $eventRepo = $registry->getRepository(Event::class);
        /* @var Event[] $events */
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
     */
    private function constructEventOffer(ManagerRegistry $registry, $values): false|EventOffer
    {
        // check POST parameters
        $required = ['sender_event_ids', 'receiver_event_ids', 'sender_clinic_id', 'receiver_doctor_id', 'receiver_clinic_id', 'description'];
        foreach ($required as $item) {
            if (!isset($values[$item])) {
                return false;
            }
        }
        if (\count($values) > \count($required)) {
            return false;
        }

        // get receiver stuff
        $receiverEventIds = $this->getEventsFromIds($registry, $values['receiver_event_ids']);
        $receiverEvents = $registry->getRepository(Event::class)->findBy(['id' => array_values($receiverEventIds)]);
        $receiverClinic = $registry->getRepository(Clinic::class)->find((int) $values['receiver_clinic_id']);
        $receiverDoctor = $registry->getRepository(Doctor::class)->find((int) $values['receiver_doctor_id']);

        // get sender stuff
        $senderEventIds = $this->getEventsFromIds($registry, $values['sender_event_ids']);
        $senderEvents = $registry->getRepository(Event::class)->findBy(['id' => array_values($senderEventIds)]);
        $senderClinic = $registry->getRepository(Clinic::class)->find((int) $values['sender_clinic_id']);
        $senderDoctor = $this->getUser();

        // construct the offer
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

        // save if offer is valid
        if ($eventOffer->isValid()) {
            DoctrineHelper::persistAndFlush($registry, ...[$eventOffer]);

            return $eventOffer;
        }

        return false;
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    #[Route(path: '/create', name: 'api_trade_create')]
    public function create(Request $request, EmailService $emailService, ManagerRegistry $registry, TranslatorInterface $translator): Response
    {
        // try to construct offer from POST values
        $eventOffer = $this->constructEventOffer($registry, json_decode($request->getContent(), true));
        if (!$eventOffer) {
            $this->displayError($translator->trans('index.danger.trade_offer_invalid', [], 'trade'));

            return new Response('NACK');
        }

        // send out all authorization request emails
        $emailService->sendActionEmail(
            $eventOffer->getReceiver()->getEmail(),
            $translator->trans('emails.new_offer.subject', [], 'trade'),
            $translator->trans('emails.new_offer.message', [], 'trade'),
            $translator->trans('emails.new_offer.action_text', [], 'trade'),
            $this->generateUrl('index_index', [], UrlGeneratorInterface::ABSOLUTE_URL));

        $this->displaySuccess($translator->trans('index.success.created_trade_offer', [], 'trade'));

        return new Response('ACK');
    }
}
