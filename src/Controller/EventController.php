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
use App\Controller\Traits\EventControllerTrait;
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
    use EventControllerTrait;

    /**
     * @Route("/assign", name="event_assign")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
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
        $persons = $member->getFrontendUsers();
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
                    $selectedPersonId = (int)$value;
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
                        $event->setFrontendUser($selectedPerson);
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
     * @Route("/confirm/person/{event}", name="event_confirm_person")
     *
     * @param Event $event
     * @param TranslatorInterface $translator
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

        if ($this->canConfirmEvent($member, $event) && $event->getFrontendUser()->getId() === $this->getPerson()->getId()) {
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
     * @param Member $member
     * @param Event $event
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
     * @Route("/confirm/member/{event}", name="event_confirm_member")
     *
     * @param Event $event
     * @param TranslatorInterface $translator
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

        if ($this->canConfirmEvent($member, $event) && null === $event->getFrontendUser() && $event->getMember()->getId() === $this->getMember()->getId()) {
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
     * @param TranslatorInterface $translator
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
                $event->getFrontendUser() instanceof Person ? EventChangeType::CONFIRMED_BY_PERSON : EventChangeType::CONFIRMED_BY_MEMBER
            );
            $this->fastSave($event, $eventPast);
        }
        $this->displaySuccess($translator->trans('confirm.messages.confirm_all_successful', ['%count%' => count($events)], 'event'));

        return $this->redirectToRoute('event_confirm');
    }

    /**
     * @Route("/search", name="event_search")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
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
            return $this->renderCsv("export.csv", $this->toDataTable($eventLineModels, $translator));
        }
        $arr['event_line_models'] = $eventLineModels;

        $arr['event_lines'] = $this->getOrganisation()->getEventLines();
        $arr['members'] = $this->getOrganisation()->getMembers();
        $arr['member'] = $member;
        $persons = [];
        foreach ($this->getOrganisation()->getMembers() as $lMember) {
            foreach ($lMember->getPersons() as $lPerson) {
                $persons[$lPerson->getId()] = $lPerson;
            }
        }
        $arr['persons'] = $persons;

        $arr['selected_member'] = $searchEventModel->getFilterMember();
        $arr['selected_person'] = $searchEventModel->getFilterFrontendUser();
        $arr['selected_event_line'] = $searchEventModel->getFilterEventLine();
        $arr['start_date_time'] = $searchEventModel->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
        $arr['end_date_time'] = $searchEventModel->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);

        return $this->renderWithBackUrl('event/search.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }

}
