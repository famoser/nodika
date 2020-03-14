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

        return $this->returnDoctors($result);
    }

    /**
     * @Route("/events/{doctor}", name="assign_events")
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function eventsAction(Doctor $doctor)
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

        return $this->returnEvents($events);
    }

    /**
     * @Route("/assign/{event}/{doctor}", name="assign_assign")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction(Event $event, Doctor $doctor)
    {
        $event->setDoctor($doctor);
        $eventPast = EventPast::create($event, EventChangeType::DOCTOR_ASSIGNED, $this->getUser());
        $this->fastSave($event, $eventPast);

        return $this->returnEvents($event);
    }
}
