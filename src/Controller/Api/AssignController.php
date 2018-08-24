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
use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\EventPast;
use App\Entity\Setting;
use App\Enum\EventChangeType;
use App\Model\Event\SearchModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/assign")
 */
class AssignController extends BaseApiController
{
    /**
     * @Route("/doctors", name="assign_doctors")
     *
     * @param SerializerInterface $serializer
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function doctorsAction(SerializerInterface $serializer)
    {
        $result = [];

        //array of users
        $user = $this->getUser();
        foreach ($user->getClinics() as $clinic) {
            foreach ($clinic->getDoctors() as $doctor) {
                $result[$doctor->getId()] = $doctor;
            }
        }
        $result = array_values($result);

        return new JsonResponse($serializer->serialize($result, 'json', ['attributes' => ['id', 'fullName', 'clinics' => ['name']]]), 200, [], true);
    }

    /**
     * @Route("/events/{doctor}", name="assign_events")
     *
     * @param SerializerInterface $serializer
     * @param Doctor              $doctor
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function eventsAction(SerializerInterface $serializer, Doctor $doctor)
    {
        //get all common clinics of current user & selected one
        $allowedFilter = [];
        foreach ($this->getUser()->getClinics() as $clinic) {
            $allowedFilter[] = $clinic->getId();
        }
        $clinics = [];
        foreach ($doctor->getClinics() as $clinic) {
            if (\in_array($clinic->getId(), $allowedFilter, true)) {
                $clinics[] = $clinic;
            }
        }

        //get all assignable events
        $settings = $this->getDoctrine()->getRepository(Setting::class)->findSingle();

        //get the events for the doctor
        $searchModel = new SearchModel(SearchModel::NONE);
        $searchModel->setClinics($clinics);
        $searchModel->setEndDateTime((new \DateTime())->add(new \DateInterval('P'.$settings->getCanConfirmDaysAdvance().'D')));
        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchModel);

        return new JsonResponse($serializer->serialize($events, 'json', ['attributes' => ['id', 'startDateTime', 'endDateTime', 'clinic' => ['name'], 'doctor' => ['id', 'fullName']]]), 200, [], true);
    }

    /**
     * @Route("/assign/{event}/{doctor}", name="assign_assign")
     *
     * @param SerializerInterface $serializer
     * @param Event               $event
     * @param Doctor              $doctor
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction(SerializerInterface $serializer, Event $event, Doctor $doctor)
    {
        $event->setDoctor($doctor);
        $eventPast = EventPast::create($event, EventChangeType::PERSON_ASSIGNED_BY_CLINIC, $this->getUser());
        $this->fastSave($event, $eventPast);

        return new JsonResponse($serializer->serialize($event, 'json', ['attributes' => ['id', 'startDateTime', 'endDateTime', 'clinic' => ['name'], 'doctor' => ['id', 'fullName']]]), 200, [], true);
    }
}
