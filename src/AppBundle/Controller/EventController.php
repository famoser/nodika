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
use AppBundle\Entity\Person;
use AppBundle\Enum\EventChangeType;
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Security\Voter\EventVoter;
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

        $assignableEvents = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findAssignableEventsAsIdArray($member);
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

        //todo
        $arr["events"] = [];
        return $this->renderWithBackUrl("event/confirm.html.twig", $arr, $this->generateUrl("dashboard_index"));
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

        $arr["eventLineModels"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($organisation, $startDateTime, $endDateTime, $member, $person, 4000);
        $arr["member"] = $member;
        $arr["members"] = $this->getOrganisation()->getMembers();
        $persons = [];
        foreach ($this->getOrganisation()->getMembers() as $member) {
            $persons[$member->getId()] = $member;
        }
        $arr["person"] = $person;
        $arr["persons"] = $persons;
        $arr["startDateTime"] = $startDateTime->format(DateTimeFormatter::DATE_TIME_FORMAT);
        $arr["endDateTime"] = $endDateTime->format(DateTimeFormatter::DATE_TIME_FORMAT);

        return $this->renderWithBackUrl("event/search.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }
}