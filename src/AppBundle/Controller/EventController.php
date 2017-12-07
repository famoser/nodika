<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;

use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\Event;
use AppBundle\Entity\Member;
use AppBundle\Entity\Person;
use AppBundle\Enum\EventChangeType;
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Model\Event\SearchEventModel;
use AppBundle\Model\EventLine\EventLineModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseFrontendController
{
    /**
     * @Route("/assign", name="event_assign")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $assignableEvents = $this->getDoctrine()->getRepository("AppBundle:Member")->findAssignableEventsAsIdArray($member);
        $persons = $member->getPersons();
        $selectedPerson = $this->getPerson();

        if ($request->getMethod() == "POST") {
            /* @var Event[] $events */
            $events = [];
            /* @var Person $selectedPerson */
            $selectedPerson = null;
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "event_") === 0) {
                    $eventId = substr($key, 6); //cut off event_
                    if (isset($assignableEvents[$eventId])) {
                        $events[] = $assignableEvents[$eventId];
                    }
                } else if ($key == "selected_person") {
                    $selectedPersonId = $value;
                    foreach ($persons as $person) {
                        if ($person->getId() == $selectedPersonId) {
                            $selectedPerson = $person;
                        }
                    }
                }
            }

            $trans = $this->get("translator");
            if (count($events) > 0) {
                if ($selectedPerson != null) {
                    $eventPastService = $this->get("app.event_past_evaluation_service");
                    $count = 0;
                    foreach ($events as $event) {
                        $oldEvent = clone ($event);
                        $event->setPerson($selectedPerson);
                        $eventPast = $eventPastService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::PERSON_ASSIGNED_BY_MEMBER);
                        $this->fastSave($eventPast, $event);
                        $count++;
                    }
                    $this->displaySuccess($trans->trans("assign.messages.assigned", ["%count%" => $count], "event"));
                } else {
                    $this->displayError($trans->trans("assign.messages.no_person", [], "event"));
                }
            } else {
                $this->displayError($trans->trans("assign.messages.no_events", [], "event"));
            }

        }


        $arr["events"] = $assignableEvents;
        $arr["member"] = $member;
        $arr["person"] = $selectedPerson;
        $arr["persons"] = $persons;
        return $this->renderWithBackUrl("event/assign.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }

    /**
     * @Route("/confirm", name="event_confirm")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $arr["events"] = $this->getDoctrine()->getRepository("AppBundle:Member")->findUnconfirmedEvents($member, $this->getPerson());
        return $this->renderWithBackUrl("event/confirm.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }

    /**
     * @param Member $member
     * @param Event $event
     * @return bool
     */
    private function canConfirmEvent(Member $member, Event $event)
    {
        $availableEvents = $this->getDoctrine()->getRepository("AppBundle:Member")->findUnconfirmedEvents($member, $this->getPerson());
        foreach ($availableEvents as $availableEvent) {
            if ($availableEvent->getId() == $event->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @Route("/confirm/person/{event}", name="event_confirm_person")
     * @param Request $request
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmPersonAction(Request $request, Event $event)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }


        $trans = $this->get("translator");
        if ($this->canConfirmEvent($member, $event) && $event->getPerson()->getId() == $this->getPerson()->getId()) {
            $oldEvent = clone($event);
            $event->setIsConfirmed(true);
            $event->setIsConfirmedDateTime(new \DateTime());
            $eventPastService = $this->get("app.event_past_evaluation_service");
            $eventPast = $eventPastService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::CONFIRMED_BY_PERSON);
            $this->fastSave($eventPast, $event);
            $this->displaySuccess($trans->trans("confirm.messages.confirm_successful", [], "event"));
        } else {
            $this->displayError($trans->trans("confirm.messages.no_access", [], "event"));
        }

        return $this->redirectToRoute("event_confirm");
    }

    /**
     * @Route("/confirm/member/{event}", name="event_confirm_member")
     * @param Request $request
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmMemberAction(Request $request, Event $event)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }


        $trans = $this->get("translator");
        if ($this->canConfirmEvent($member, $event) && $event->getPerson() == null && $event->getMember()->getId() == $this->getMember()->getId()) {
            $oldEvent = clone($event);
            $event->setIsConfirmed(true);
            $event->setIsConfirmedDateTime(new \DateTime());
            $eventPastService = $this->get("app.event_past_evaluation_service");
            $eventPast = $eventPastService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::CONFIRMED_BY_MEMBER);
            $this->fastSave($eventPast, $event);
            $this->displaySuccess($trans->trans("confirm.messages.confirm_successful", [], "event"));
        } else {
            $this->displayError($trans->trans("confirm.messages.no_access", [], "event"));
        }

        return $this->redirectToRoute("event_confirm");
    }

    /**
     * @Route("/confirm/all", name="event_confirm_all")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAllAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }


        $eventPastService = $this->get("app.event_past_evaluation_service");
        $trans = $this->get("translator");

        $person = $this->getPerson();
        $events = $this->getDoctrine()->getRepository("AppBundle:Member")->findUnconfirmedEvents($member, $person);
        foreach ($events as $event) {
            $oldEvent = clone($event);
            $event->setIsConfirmed(true);
            $event->setIsConfirmedDateTime(new \DateTime());
            $eventPast = $eventPastService->createEventPast(
                $person,
                $oldEvent,
                $event,
                $event->getPerson() instanceof Person ? EventChangeType::CONFIRMED_BY_PERSON : EventChangeType::CONFIRMED_BY_MEMBER
            );
            $this->fastSave($event, $eventPast);
        }
        $this->displaySuccess($trans->trans("confirm.messages.confirm_all_successful", ["%count%" => count($events)], "event"));
        return $this->redirectToRoute("event_confirm");
    }

    /**
     * @param Request $request
     * @param Member $member
     * @return SearchEventModel
     */
    private function resolveSearchEventModel(Request $request, Member $member)
    {
        $organisation = $member->getOrganisation();

        $startQuery = $request->query->get("start");
        $startDateTime = false;
        if ($startQuery != "") {
            $startDateTime = new \DateTime($startQuery);
        }
        if (!$startDateTime) {
            $startDateTime = new \DateTime();
        }

        $endQuery = $request->query->get("end");
        $endDateTime = false;
        if ($endQuery != "") {
            $endDateTime = new \DateTime($endQuery);
        }
        if (!$endDateTime) {
            $endDateTime = clone($startDateTime);
            $endDateTime->add(new \DateInterval("P1Y"));
        }

        $memberQuery = $request->query->get("member");
        $member = null;
        if (is_numeric($memberQuery)) {
            foreach ($organisation->getMembers() as $organisationMember) {
                if ($organisationMember->getId() == $memberQuery) {
                    $member = $organisationMember;
                }
            }
        }

        $personQuery = $request->query->get("person");
        $person = null;
        if (is_numeric($personQuery)) {
            foreach ($organisation->getMembers() as $organisationMember) {
                foreach ($organisationMember->getPersons() as $organisationPerson) {
                    if ($organisationPerson->getId() == $personQuery) {
                        $person = $organisationPerson;
                    }
                }
            }
        }

        $searchEventModel = new SearchEventModel($organisation, $startDateTime);
        $searchEventModel->setEndDateTime($endDateTime);
        $searchEventModel->setFilterMember($member);
        $searchEventModel->setFilterPerson($person);


        return $searchEventModel;
    }


    /**
     * @Route("/search", name="event_search")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $organisationRepo = $this->getDoctrine()->getRepository("AppBundle:Organisation");

        $searchEventModel = $this->resolveSearchEventModel($request, $member);
        $eventLineModels = $organisationRepo->findEventLineModels($searchEventModel);

        if ($request->query->get("view") == "csv") {
            return $this->exportAsCsv($eventLineModels);
        } else {
            $arr["eventLineModels"] = $eventLineModels;
            $arr["members"] = $this->getOrganisation()->getMembers();
            $persons = [];
            foreach ($this->getOrganisation()->getMembers() as $lMember) {
                foreach ($lMember->getPersons() as $lPerson) {
                    $persons[$lPerson->getId()] = $lPerson;
                }
            }
            $arr["persons"] = $persons;

            $arr["selected_member"] = $searchEventModel->getFilterMember();
            $arr["member"] = $member;
            $arr["selected_person"] = $searchEventModel->getFilterPerson();
            $arr["startDateTime"] = $searchEventModel->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
            $arr["endDateTime"] = $searchEventModel->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);

            return $this->renderWithBackUrl("event/search.html.twig", $arr, $this->generateUrl("dashboard_index"));
        }
    }

    /**
     * @param EventLineModel[] $eventModels
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportAsCsv($eventModels)
    {
        $data = [];
        foreach ($eventModels as $eventModel) {
            $row = [];
            $row[] = $eventModel->eventLine->getName();
            $row[] = $eventModel->eventLine->getDescription();
            $data[] = $row;
            $data[] = $this->getEventsHeader();
            foreach ($eventModel->events as $event) {
                $row = [];
                $row[] = $event->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
                $row[] = $event->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
                $row[] = $event->getMember()->getName();
                if ($event->getPerson() instanceof Person) {
                    $row[] = $event->getPerson()->getFullName();
                }
                $data[] = $row;
            }
            $data[] = [];
        }

        return $this->renderCsv("export.csv", $data);
    }

    /**
     * @return string[]
     */
    private function getEventsHeader()
    {
        $start = $this->get("translator")->trans("start_date_time", [], "entity_event");
        $end = $this->get("translator")->trans("end_date_time", [], "entity_event");
        $member = $this->get("translator")->trans("entity.name", [], "entity_member");
        $person = $this->get("translator")->trans("entity.name", [], "entity_person");
        return [$start, $end, $member, $person];
    }
}