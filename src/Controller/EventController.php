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

use App\Controller\Base\BaseFrontendController;
use App\Entity\Event;
use App\Entity\Member;
use App\Entity\Person;
use App\Enum\EventChangeType;
use App\Helper\DateTimeFormatter;
use App\Model\Event\SearchEventModel;
use App\Model\EventLine\EventLineModel;
use App\Service\EventPastEvaluationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/event")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseFrontendController
{
    /**
     * @Route("/assign", name="event_assign")
     *
     * @param Request                    $request
     * @param TranslatorInterface        $translator
     * @param EventPastEvaluationService $eventPastEvaluationService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction(Request $request, TranslatorInterface $translator, EventPastEvaluationService $eventPastEvaluationService)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $assignableEvents = $this->getDoctrine()->getRepository('App:Member')->findAssignableEventsAsIdArray($member);
        $persons = $member->getPersons();
        $selectedPerson = $this->getPerson();

        if ('POST' === $request->getMethod()) {
            /* @var Event[] $events */
            $events = [];
            /* @var Person $selectedPerson */
            $selectedPerson = null;
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'event_')) {
                    $eventId = mb_substr($key, 6); //cut off event_
                    if (isset($assignableEvents[$eventId])) {
                        $events[] = $assignableEvents[$eventId];
                    }
                } elseif ('selected_person' === $key) {
                    $selectedPersonId = (int) $value;
                    foreach ($persons as $person) {
                        if ($person->getId() === $selectedPersonId) {
                            $selectedPerson = $person;
                        }
                    }
                }
            }

            if (count($events) > 0) {
                if (null !== $selectedPerson) {
                    $count = 0;
                    foreach ($events as $event) {
                        $oldEvent = clone $event;
                        $event->setPerson($selectedPerson);
                        $eventPast = $eventPastEvaluationService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::PERSON_ASSIGNED_BY_MEMBER);
                        $this->fastSave($eventPast, $event);
                        ++$count;
                    }
                    $this->displaySuccess($translator->trans('assign.messages.assigned', ['%count%' => $count], 'event'));
                } else {
                    $this->displayError($translator->trans('assign.messages.no_person', [], 'event'));
                }
            } else {
                $this->displayError($translator->trans('assign.messages.no_events', [], 'event'));
            }
        }

        $arr['events'] = $assignableEvents;
        $arr['member'] = $member;
        $arr['person'] = $selectedPerson;
        $arr['persons'] = $persons;

        return $this->renderWithBackUrl('event/assign.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }

    /**
     * @Route("/confirm", name="event_confirm")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAction()
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $arr['events'] = $this->getDoctrine()->getRepository('App:Member')->findUnconfirmedEvents($member, $this->getPerson());

        return $this->renderWithBackUrl('event/confirm.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }

    /**
     * @param Member $member
     * @param Event  $event
     *
     * @return bool
     */
    private function canConfirmEvent(Member $member, Event $event)
    {
        $availableEvents = $this->getDoctrine()->getRepository('App:Member')->findUnconfirmedEvents($member, $this->getPerson());
        foreach ($availableEvents as $availableEvent) {
            if ($availableEvent->getId() === $event->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @Route("/confirm/person/{event}", name="event_confirm_person")
     *
     * @param Event                      $event
     * @param TranslatorInterface        $translator
     * @param EventPastEvaluationService $eventPastEvaluationService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmPersonAction(Event $event, TranslatorInterface $translator, EventPastEvaluationService $eventPastEvaluationService)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        if ($this->canConfirmEvent($member, $event) && $event->getPerson()->getId() === $this->getPerson()->getId()) {
            $oldEvent = clone $event;
            $event->setIsConfirmed(true);
            $event->setIsConfirmedDateTime(new \DateTime());
            $eventPast = $eventPastEvaluationService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::CONFIRMED_BY_PERSON);
            $this->fastSave($eventPast, $event);
            $this->displaySuccess($translator->trans('confirm.messages.confirm_successful', [], 'event'));
        } else {
            $this->displayError($translator->trans('confirm.messages.no_access', [], 'event'));
        }

        return $this->redirectToRoute('event_confirm');
    }

    /**
     * @Route("/confirm/member/{event}", name="event_confirm_member")
     *
     * @param Event                      $event
     * @param TranslatorInterface        $translator
     * @param EventPastEvaluationService $eventPastEvaluationService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmMemberAction(Event $event, TranslatorInterface $translator, EventPastEvaluationService $eventPastEvaluationService)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        if ($this->canConfirmEvent($member, $event) && null === $event->getPerson() && $event->getMember()->getId() === $this->getMember()->getId()) {
            $oldEvent = clone $event;
            $event->setIsConfirmed(true);
            $event->setIsConfirmedDateTime(new \DateTime());
            $eventPast = $eventPastEvaluationService->createEventPast($this->getPerson(), $oldEvent, $event, EventChangeType::CONFIRMED_BY_MEMBER);
            $this->fastSave($eventPast, $event);
            $this->displaySuccess($translator->trans('confirm.messages.confirm_successful', [], 'event'));
        } else {
            $this->displayError($translator->trans('confirm.messages.no_access', [], 'event'));
        }

        return $this->redirectToRoute('event_confirm');
    }

    /**
     * @Route("/confirm/all", name="event_confirm_all")
     *
     * @param TranslatorInterface        $translator
     * @param EventPastEvaluationService $eventPastEvaluationService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAllAction(TranslatorInterface $translator, EventPastEvaluationService $eventPastEvaluationService)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $person = $this->getPerson();
        $events = $this->getDoctrine()->getRepository('App:Member')->findUnconfirmedEvents($member, $person);
        foreach ($events as $event) {
            $oldEvent = clone $event;
            $event->setIsConfirmed(true);
            $event->setIsConfirmedDateTime(new \DateTime());
            $eventPast = $eventPastEvaluationService->createEventPast(
                $person,
                $oldEvent,
                $event,
                $event->getPerson() instanceof Person ? EventChangeType::CONFIRMED_BY_PERSON : EventChangeType::CONFIRMED_BY_MEMBER
            );
            $this->fastSave($event, $eventPast);
        }
        $this->displaySuccess($translator->trans('confirm.messages.confirm_all_successful', ['%count%' => count($events)], 'event'));

        return $this->redirectToRoute('event_confirm');
    }

    /**
     * @param Request $request
     * @param Member  $member
     *
     * @return SearchEventModel
     */
    private function resolveSearchEventModel(Request $request, Member $member)
    {
        $organisation = $member->getOrganisation();

        $startQuery = $request->query->get('start');
        $startDateTime = new \DateTime($startQuery);

        $endQuery = $request->query->get('end');
        $endDateTime = false;
        if (mb_strlen($endQuery) > 0) {
            $endDateTime = new \DateTime($endQuery);
        }
        if (!$endDateTime) {
            $endDateTime = clone $startDateTime;
            $endDateTime = $endDateTime->add(new \DateInterval('P1Y'));
        }

        $memberQuery = $request->query->get('member');
        $member = null;
        if (is_numeric($memberQuery)) {
            $memberQueryInt = (int) $memberQuery;
            foreach ($organisation->getMembers() as $organisationMember) {
                if ($organisationMember->getId() === $memberQueryInt) {
                    $member = $organisationMember;
                }
            }
        }

        $personQuery = $request->query->get('person');
        $person = null;
        if (is_numeric($personQuery)) {
            $personQueryInt = (int) $personQuery;
            foreach ($organisation->getMembers() as $organisationMember) {
                foreach ($organisationMember->getPersons() as $organisationPerson) {
                    if ($organisationPerson->getId() === $personQueryInt) {
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
     *
     * @param Request             $request
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request, TranslatorInterface $translator)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $organisationRepo = $this->getDoctrine()->getRepository('App:Organisation');

        $searchEventModel = $this->resolveSearchEventModel($request, $member);
        $eventLineModels = $organisationRepo->findEventLineModels($searchEventModel);

        if ('csv' === $request->query->get('view')) {
            return $this->exportAsCsv($eventLineModels, $translator);
        }
        $arr['eventLineModels'] = $eventLineModels;
        $arr['members'] = $this->getOrganisation()->getMembers();
        $persons = [];
        foreach ($this->getOrganisation()->getMembers() as $lMember) {
            foreach ($lMember->getPersons() as $lPerson) {
                $persons[$lPerson->getId()] = $lPerson;
            }
        }
        $arr['persons'] = $persons;

        $arr['selected_member'] = $searchEventModel->getFilterMember();
        $arr['member'] = $member;
        $arr['selected_person'] = $searchEventModel->getFilterPerson();
        $arr['startDateTime'] = $searchEventModel->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
        $arr['endDateTime'] = $searchEventModel->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);

        return $this->renderWithBackUrl('event/search.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }

    /**
     * @param EventLineModel[]    $eventModels
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportAsCsv($eventModels, TranslatorInterface $translator)
    {
        $data = [];
        foreach ($eventModels as $eventModel) {
            $row = [];
            $row[] = $eventModel->eventLine->getName();
            $row[] = $eventModel->eventLine->getDescription();
            $data[] = $row;
            $data[] = $this->getEventsHeader($translator);
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

        return $this->renderCsv('export.csv', $data);
    }

    /**
     * @param TranslatorInterface $translator
     *
     * @return string[]
     */
    private function getEventsHeader(TranslatorInterface $translator)
    {
        $start = $translator->trans('start_date_time', [], 'entity_event');
        $end = $translator->trans('end_date_time', [], 'entity_event');
        $member = $translator->trans('entity.name', [], 'entity_member');
        $person = $translator->trans('entity.name', [], 'entity_person');

        return [$start, $end, $member, $person];
    }
}
