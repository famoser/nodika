<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration\Organisation;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\Organisation;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\EventLine\EventLineType;
use AppBundle\Form\Generic\RemoveThingType;
use AppBundle\Security\Voter\EventLineVoter;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/event_line")
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
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $eventLine = new EventLine();
        $eventLine->setOrganisation($organisation);
        $newEventLineForm = $this->handleDoctrineFormWithCustomOnSuccess(
            $this->createCrudForm(EventLineType::class, SubmitButtonType::CREATE),
            $request,
            $eventLine,
            function ($form, $entity) use ($organisation) {
                return $this->redirectToRoute("administration_organisation_event_line_administer", ["organisation" => $organisation->getId()]);
            }
        );
        if ($newEventLineForm instanceof Response) {
            return $newEventLineForm;
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["new_event_line_form"] = $newEventLineForm->createView();
        return $this->render(
            'administration/organisation/event_line/new.html.twig', $arr
        );
    }

    /**
     * @Route("/{eventLine}/administer", name="administration_organisation_event_line_administer")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function administerAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;

        return $this->render(
            'administration/organisation/event_line/administer.html.twig', $arr
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

        $editEventLineForm = $this->createForm(EventLineType::class);
        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;

        $editEventLineForm->setData($eventLine);
        $editEventLineForm->handleRequest($request);

        if ($editEventLineForm->isSubmitted()) {
            if ($editEventLineForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($eventLine);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.event_line_save", [], "event_line"));
                return $this->redirectToRoute("administration_organisation_events", ["organisation" => $organisation->getId()]);
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
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;

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