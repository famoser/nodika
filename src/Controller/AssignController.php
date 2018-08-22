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
use App\Entity\EventPast;
use App\Entity\Doctor;
use App\Entity\Setting;
use App\Enum\EventChangeType;
use App\Model\Breadcrumb;
use App\Model\Event\SearchModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/assign")
 * @Security("has_role('ROLE_USER')")
 */
class AssignController extends BaseFormController
{
    /**
     * @Route("/", name="assign_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction()
    {
        $searchEventModel = new SearchModel(SearchModel::YEAR);
        $searchEventModel->setClinics($this->getUser()->getClinics());

        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchEventModel);

        $arr["events"] = $events;
        return $this->render('assign/index.html.twig', $arr);
    }

    /**
     * @Route("/api/assignable_users", name="assign_assignable_users")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function apiAssignableUsersAction(SerializerInterface $serializer)
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

        return new JsonResponse($serializer->serialize($result, "json", ["attributes" => ["id", "fullName", "clinics" => ["name"]]]), 200, [], true);
    }

    /**
     * @Route("/api/assignable_events/{doctor}", name="assign_assignable_events")
     *
     * @param SerializerInterface $serializer
     * @param Doctor $doctor
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function apiAssignableEventsAction(SerializerInterface $serializer, Doctor $doctor)
    {
        //get all common clinics of current user & selected one
        $allowedFilter = [];
        foreach ($this->getUser()->getClinics() as $clinic) {
            $allowedFilter[] = $clinic->getId();
        }
        $clinics = [];
        foreach ($doctor->getClinics() as $clinic) {
            if (in_array($clinic->getId(), $allowedFilter)) {
                $clinics[] = $clinic;
            }
        }

        //get all assignable events
        $settings = $this->getDoctrine()->getRepository(Setting::class)->findSingle();

        $searchModel = new SearchModel(SearchModel::NONE);
        $searchModel->setClinics($clinics);
        $searchModel->setEndDateTime((new \DateTime())->add(new \DateInterval("P" . $settings->getCanConfirmDaysAdvance() . "D")));
        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchModel);

        return new JsonResponse($serializer->serialize($events, "json", ["attributes" => ["id", "startDateTime", "endDateTime", "clinic" => ["name"], "doctor" => ["id", "fullName"]]]), 200, [], true);
    }

    /**
     * @Route("/api/assign/{event}/{doctor}", name="assign_assign")
     *
     * @param SerializerInterface $serializer
     * @param Event $event
     * @param Doctor $doctor
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function apiAssignAction(SerializerInterface $serializer, Event $event, Doctor $doctor)
    {
        $event->setDoctor($doctor);
        $eventPast = EventPast::create($event, EventChangeType::PERSON_ASSIGNED_BY_CLINIC, $this->getUser());
        $this->fastSave($event, $eventPast);

        return new JsonResponse($serializer->serialize($event, "json", ["attributes" => ["id", "startDateTime", "endDateTime", "clinic" => ["name"], "doctor" => ["id", "fullName"]]]), 200, [], true);
    }

    /**
     * @return Breadcrumb[]|array
     */
    public function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl("index_index"),
                $this->getTranslator()->trans("index.title", [], "index")
            )
        ];

    }
}
