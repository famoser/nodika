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
use App\Controller\Traits\EventControllerTrait;
use App\Entity\Event;
use App\Entity\EventLine;
use App\Entity\EventPast;
use App\Enum\EventChangeType;
use App\Form\Model\Event\SearchType;
use App\Helper\DateTimeFormatter;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\CsvServiceInterface;
use App\Service\Interfaces\SettingServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/event")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseFormController
{
    use EventControllerTrait;

    /**
     * @Route("/assign", name="event_assign")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction(Request $request, TranslatorInterface $translator)
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
     * @Route("/{event}/confirm", name="event_confirm_member")
     *
     * @param Event $event
     *
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmMemberAction(Event $event, TranslatorInterface $translator)
    {
        $event->setConfirmDateTime(new \DateTime());
        $eventPast = new EventPast($event, EventChangeType::CONFIRMED_BY_PERSON, $this->getUser());
        $this->fastSave($eventPast, $event);
        $this->displaySuccess($translator->trans('confirm.messages.confirm_successful', [], 'event'));
        return $this->redirectToRoute('event_confirm');
    }

    /**
     * @Route("/confirm/all", name="event_confirm_all")
     *
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAllAction(TranslatorInterface $translator, SettingServiceInterface $settingService)
    {
        $searchModel = new SearchModel();
        $searchModel->setIsConfirmed(false);
        $searchModel->setFrontendUser($this->getUser());
        $end = new \DateTime();
        $end->add($settingService->getCanConfirmEventAt());
        $searchModel->setStartDateTime(new \DateTime());
        $searchModel->setEndDateTime($end);

        $eventLines = $this->getDoctrine()->getRepository('App:EventLine')->findEventLineModels($searchModel);

        $manager = $this->getDoctrine()->getManager();
        $total = 0;
        foreach ($eventLines as $eventLine) {
            foreach ($eventLine->events as $event) {
                $event->setConfirmDateTime(new \DateTime());
                $eventPast = new EventPast($event, EventChangeType::CONFIRMED_BY_PERSON, $this->getUser());
                $manager->persist($event);
                $manager->persist($eventPast);
                $total++;
            }
        }
        $manager->flush();

        $this->displaySuccess($translator->trans('confirm.messages.confirm_all_successful', ['%count%' => $total], 'event'));

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
    public function searchAction(Request $request, TranslatorInterface $translator, CsvServiceInterface $csvService)
    {
        $searchModel = new SearchModel();

        $export = false;
        $form = $this->handleForm(
            $this->createForm(SearchType::class, $searchModel)
                ->add("search", SubmitType::class)
                ->add("export", SubmitType::class),
            $request,
            function ($form) use (&$export) {
                /* @var Form $form */
                $export = $form->get('export')->isClicked();
                return $form;
            }
        );

        $eventLineRepo = $this->getDoctrine()->getRepository(EventLine::class);
        $eventLineModels = $eventLineRepo->findEventLineModels($searchModel);

        if ($export) {
            return $csvService->renderCsv("export.csv", $this->toDataTable($eventLineModels, $translator), $this->getEventsHeader($translator));
        }

        $arr["event_line_models"] = $eventLineModels;
        $arr["search_form"] = $form;

        return $this->render('event/search.html.twig', $arr);
    }

}
