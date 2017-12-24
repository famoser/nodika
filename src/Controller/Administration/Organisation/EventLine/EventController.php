<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Organisation\EventLine;

use App\Controller\Base\BaseController;
use App\Entity\Event;
use App\Entity\EventLine;
use App\Entity\Organisation;
use App\Enum\EventChangeType;
use App\Enum\SubmitButtonType;
use App\Model\EventPast\EventPastEvaluation;
use App\Security\Voter\EventLineVoter;
use App\Security\Voter\EventVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/events")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_event_line_event_new")
     *
     * @param Request      $request
     * @param Organisation $organisation
     * @param EventLine    $eventLine
     *
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::EDIT, $eventLine);

        $event = new Event();
        $event->setEventLine($eventLine);
        $myForm = $this->handleCrudForm(
            $request,
            $event,
            SubmitButtonType::CREATE,
            function ($form, $entity) use ($organisation, $eventLine) {
                /* @var Form $form */
                /* @var Event $entity */
                $myService = $this->get('app.event_past_evaluation_service');
                $eventPast = $myService->createEventPast($this->getPerson(), null, $entity, EventChangeType::MANUALLY_CREATED_BY_ADMIN);
                $this->fastSave($eventPast);

                return $this->redirectToRoute('administration_organisation_event_line_event_new', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()]);
            },
            ['organisation' => $organisation]
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['new_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/event/new.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }

    /**
     * @Route("/{event}/edit", name="administration_organisation_event_line_event_edit")
     *
     * @param Request      $request
     * @param Organisation $organisation
     * @param EventLine    $eventLine
     * @param Event        $event
     *
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, EventLine $eventLine, Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $oldEvent = clone $event;
        $myForm = $this->handleCrudForm(
            $request,
            $event,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation, $eventLine, $oldEvent) {
                /* @var Form $form */
                /* @var Event $entity */
                $myService = $this->get('app.event_past_evaluation_service');
                $eventPast = $myService->createEventPast($this->getPerson(), $oldEvent, $entity, EventChangeType::MANUALLY_CHANGED_BY_ADMIN);
                $this->fastSave($eventPast);

                return $this->redirectToRoute('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()]);
            },
            ['organisation' => $organisation]
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['event'] = $event;
        $arr['edit_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/event/edit.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_event_view', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'event' => $event->getId()])
        );
    }

    /**
     * @Route("/{event}/remove", name="administration_organisation_event_line_event_remove")
     *
     * @param Request      $request
     * @param Organisation $organisation
     * @param EventLine    $eventLine
     * @param Event        $event
     *
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, EventLine $eventLine, Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::REMOVE, $event);

        $oldEvent = clone $event;
        $myForm = $this->handleCrudForm(
            $request,
            $event,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation, $eventLine, $oldEvent) {
                /* @var Form $form */
                /* @var Event $entity */
                $myService = $this->get('app.event_past_evaluation_service');
                $eventPast = $myService->createEventPast($this->getPerson(), $oldEvent, $entity, EventChangeType::MANUALLY_REMOVED_BY_ADMIN);
                $this->fastSave($eventPast);

                return $this->redirectToRoute('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['event'] = $event;
        $arr['remove_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/event/remove.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_event_view', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'event' => $event->getId()])
        );
    }

    /**
     * @Route("/{event}/view", name="administration_organisation_event_line_event_view")
     *
     * @param Request      $request
     * @param Organisation $organisation
     * @param EventLine    $eventLine
     * @param Event        $event
     *
     * @return Response
     */
    public function viewAction(Request $request, Organisation $organisation, EventLine $eventLine, Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['event'] = $event;

        $pasts = $event->getEventPast();
        $eventPastService = $this->get('app.event_past_evaluation_service');
        /* @var EventPastEvaluation[] $displayPasts */
        $displayPasts = [];
        foreach ($pasts as $past) {
            $displayPasts[] = $eventPastService->createEventPastEvaluation($past);
        }
        $arr['eventPasts'] = $displayPasts;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/event/view.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }
}
