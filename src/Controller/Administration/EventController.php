<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration;

use App\Controller\Base\BaseController;
use App\Entity\Event;
use App\Entity\EventLine;
use App\Entity\Organisation;
use App\Enum\EventChangeType;
use App\Enum\SubmitButtonType;
use App\Model\EventPast\EventPastEvaluation;
use App\Security\Voter\EventLineVoter;
use App\Security\Voter\EventVoter;
use App\Service\EventPastEvaluationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/events")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseController
{
    /**
     * @Route("/new", name="administration_event_new")
     *
     * @param Request $request
     * @param EventLine $eventLine
     * @param TranslatorInterface $translator
     * @param EventPastEvaluationService $eventPastEvaluationService
     *
     * @return Response
     */
    public function newAction(Request $request, EventLine $eventLine, TranslatorInterface $translator, EventPastEvaluationService $eventPastEvaluationService)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::EDIT, $eventLine);

        $event = new Event();
        $event->setEventLine($eventLine);
        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $event,
            SubmitButtonType::CREATE,
            function ($form, $entity) use ($organisation, $eventLine, $eventPastEvaluationService) {
                /* @var Form $form */
                /* @var Event $entity */
                $eventPast = $eventPastEvaluationService->createEventPast($this->getPerson(), null, $entity, EventChangeType::MANUALLY_CREATED_BY_ADMIN);
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
     * @Route("/{event}/edit", name="administration_event_edit")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param Event $event
     * @param TranslatorInterface $translator
     * @param EventPastEvaluationService $eventPastEvaluationService
     *
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, EventLine $eventLine, Event $event, TranslatorInterface $translator, EventPastEvaluationService $eventPastEvaluationService)
    {
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $oldEvent = clone $event;
        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $event,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation, $eventLine, $oldEvent, $eventPastEvaluationService) {
                /* @var Form $form */
                /* @var Event $entity */
                $eventPast = $eventPastEvaluationService->createEventPast($this->getPerson(), $oldEvent, $entity, EventChangeType::MANUALLY_CHANGED_BY_ADMIN);
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
     * @Route("/{event}/remove", name="administration_event_remove")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param Event $event
     * @param TranslatorInterface $translator
     * @param EventPastEvaluationService $eventPastEvaluationService
     *
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, EventLine $eventLine, Event $event, TranslatorInterface $translator, EventPastEvaluationService $eventPastEvaluationService)
    {
        $this->denyAccessUnlessGranted(EventVoter::REMOVE, $event);

        $oldEvent = clone $event;
        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $event,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation, $eventLine, $oldEvent, $eventPastEvaluationService) {
                /* @var Form $form */
                /* @var Event $entity */
                $eventPast = $eventPastEvaluationService->createEventPast($this->getPerson(), $oldEvent, $entity, EventChangeType::MANUALLY_REMOVED_BY_ADMIN);
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
     * @Route("/{event}/history", name="administration_event_history")
     *
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param Event $event
     * @param EventPastEvaluationService $eventPastEvaluationService
     *
     * @return Response
     */
    public function historyAction(Organisation $organisation, EventLine $eventLine, Event $event, EventPastEvaluationService $eventPastEvaluationService)
    {
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['event'] = $event;

        $pasts = $event->getEventPast();
        /* @var EventPastEvaluation[] $displayPasts */
        $displayPasts = [];
        foreach ($pasts as $past) {
            $displayPasts[] = $eventPastEvaluationService->createEventPastEvaluation($past);
        }
        $arr['eventPasts'] = $displayPasts;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/event/view.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }
}
