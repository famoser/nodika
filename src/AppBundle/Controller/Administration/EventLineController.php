<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\Organisation;
use AppBundle\Form\EventLine\NewEventLineType;
use AppBundle\Form\Generic\RemoveThingType;
use AppBundle\Security\Voter\EventLineVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/organisation/{organisation}/events")
 * @Security("has_role('ROLE_USER')")
 */
class EventLineController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_event_line_new")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::CREATE, $organisation);

        $newEventLineForm = $this->createForm(NewEventLineType::class);
        $arr = [];

        $eventLine = new EventLine();
        $newEventLineForm->setData($eventLine);
        $newEventLineForm->handleRequest($request);

        if ($newEventLineForm->isSubmitted()) {
            if ($newEventLineForm->isValid()) {
                $eventLine->setOrganisation($organisation);
                $em = $this->getDoctrine()->getManager();
                $em->persist($eventLine);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.event_line_add", [], "event_line"));
                $newEventLineForm = $this->createForm(NewEventLineType::class);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["new_event_line_form"] = $newEventLineForm->createView();
        return $this->render(
            'administration/organisation/event_line/new.html.twig', $arr
        );
    }

    /**
     * @Route("/{eventLine}/edit", name="administration_organisation_event_line_edit")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::EDIT, $eventLine);

        $editEventLineForm = $this->createForm(NewEventLineType::class);
        $arr = [];

        $editEventLineForm->setData($eventLine);
        $editEventLineForm->handleRequest($request);

        if ($editEventLineForm->isSubmitted()) {
            if ($editEventLineForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($eventLine);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.event_line_save", [], "event_line"));
                $editEventLineForm = $this->createForm(NewEventLineType::class);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["edit_event_line_form"] = $editEventLineForm->createView();
        return $this->render(
            'administration/organisation/event_line/edit.html.twig', $arr
        );
    }

    /**
     * @Route("/{eventLine}/remove", name="administration_organisation_event_line_remove")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::REMOVE, $eventLine);

        $removeEventLineForm = $this->createForm(RemoveThingType::class);
        $arr = [];

        $removeEventLineForm->handleRequest($request);

        if ($removeEventLineForm->isSubmitted()) {
            if ($removeEventLineForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($eventLine);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.event_line_save", [], "event_line"));
                return $this->redirectToRoute("administration_organisation_events", ["organisation" => $organisation->getId()]);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["remove_event_line_form"] = $removeEventLineForm->createView();
        return $this->render(
            'administration/organisation/event_line/remove.html.twig', $arr
        );
    }

}