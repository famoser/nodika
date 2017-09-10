<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration\Organisation\EventLine;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\Event\ImportEventsType;
use AppBundle\Form\Event\EventType;
use AppBundle\Form\Generic\RemoveThingType;
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Helper\EventPastEvaluationHelper;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Model\Event\ImportEventModel;
use AppBundle\Security\Voter\EventLineVoter;
use AppBundle\Security\Voter\EventVoter;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * @Route("/events")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_event_line_event_new")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
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
                $eventPast = EventPastEvaluationHelper::createCreatedByAdminChange($entity, $this->getPerson());
                $this->fastSave($eventPast);
                return $this->redirectToRoute("administration_organisation_event_line_event_new", ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()]);
            },
            ["organisation" => $organisation]
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["new_form"] = $myForm->createView();
        return $this->render(
            'administration/organisation/event_line/event/new.html.twig', $arr
        );
    }

    /**
     * @Route("/{event}/edit", name="administration_organisation_event_line_event_edit")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param Event $event
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, EventLine $eventLine, Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $oldEvent = clone($event);
        $myForm = $this->handleCrudForm(
            $request,
            $event,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation, $eventLine, $oldEvent) {
                /* @var Form $form */
                /* @var Event $entity */
                $eventPast = EventPastEvaluationHelper::createChangedByAdminChange($entity, $oldEvent, $this->getPerson());
                $this->fastSave($eventPast);
                return $this->redirectToRoute("administration_organisation_event_line_administer", ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()]);
            },
            ["organisation" => $organisation]
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["edit_form"] = $myForm->createView();
        return $this->render(
            'administration/organisation/event_line/event/edit.html.twig', $arr
        );
    }

    /**
     * @Route("/{event}/remove", name="administration_organisation_event_line_event_remove")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param Event $event
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, EventLine $eventLine, Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::REMOVE, $event);

        $oldEvent = clone($event);
        $myForm = $this->handleCrudForm(
            $request,
            $event,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation, $eventLine, $oldEvent) {
                /* @var Form $form */
                /* @var Event $entity */
                $eventPast = EventPastEvaluationHelper::createRemovedByAdminChange($entity, $this->getPerson());
                $this->fastSave($eventPast);
                return $this->redirectToRoute("administration_organisation_event_line_administer", ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["remove_form"] = $myForm->createView();
        return $this->render(
            'administration/organisation/event_line/event/remove.html.twig', $arr
        );
    }

    /**
     * @Route("/{event}/view", name="administration_organisation_event_line_event_view")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param Event $event
     * @return Response
     */
    public function viewAction(Request $request, Organisation $organisation, EventLine $eventLine, Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

        $arr["event"] = $event;
        return $this->render(
            'administration/organisation/event_line/event/view.html.twig', $arr
        );
    }
}