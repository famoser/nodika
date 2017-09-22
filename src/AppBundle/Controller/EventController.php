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

        $events = [];
        foreach ($member->getOrganisation()->getMembers() as $member) {
            foreach ($member->getEvents() as $event) {
                if ($event->getPerson() == null) {
                    $events[] = $event;
                }
            }
        }

        $arr["events"] = $events;
        $arr["member"] = $member;
        $arr["person"] = $this->getPerson();
        return $this->renderWithBackUrl("event/assign.html.twig", $arr, $this->generateUrl("dashboard_index"));
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

    /**
     * @Route("/{event}/view", name="event_view")
     * @param Request $request
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Event $event)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

        $arr["event"] = $event;
        return $this->renderWithBackUrl("dashboard/index.html.twig");
    }


    /**
     * @Route("/{event}/assign/{person}", name="event_assign_event")
     * @param Request $request
     * @param Event $event
     * @param Person $person
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignEventAction(Request $request, Event $event, Person $person)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        if ($member->getId() == $event->getMember()) {
            foreach ($event->getMember()->getPersons() as $eventPerson) {
                if ($eventPerson->getId() == $person->getId()) {
                    $oldEvent = clone($event);
                    $event->setPerson($person);
                    $service = $this->get("app.event_past_evaluation_service");
                    $past = $service->createEventPast($this->getPerson(), $oldEvent, $$event, EventChangeType::PERSON_ASSIGNED_BY_MEMBER);
                    $this->fastSave($past, $event);

                    $translator = $this->get("translator");
                    $this->displaySuccess($translator->trans("message.event_assigned", [], "event"));
                }
            }
        }

        return $this->redirectToRoute("event_view", ["event" => $event]);
    }

    /**
     * @Route("/assignAll/{person}", name="event_assign_all")
     * @param Request $request
     * @param Person $person
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAllAction(Request $request, Person $person)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        if ($person->getMembers()->contains($member)) {
            $em = $this->getDoctrine()->getManager();
            foreach ($member->getEvents() as $event) {
                if ($event->getPerson() == null) {
                    $event->setPerson($person);
                    $em->persist($event);
                }
            }
            $em->flush();

            $translator = $this->get("translator");
            $this->displaySuccess($translator->trans("message.all_events_assigned", [], "event"));
        }

        return $this->redirectToRoute("dashboard_index");
    }
}