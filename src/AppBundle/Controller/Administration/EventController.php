<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventPast;
use AppBundle\Entity\Organisation;
use AppBundle\Enum\EventChangeType;
use AppBundle\Form\Generic\ImportFileType;
use AppBundle\Form\Generic\RemoveThingType;
use AppBundle\Form\Event\NewEventType;
use AppBundle\Model\Form\ImportFileModel;
use AppBundle\Security\Voter\EventVoter;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/organisation/{organisation}/events")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_event_new")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);
        $arr = [];
        $arr["organisation"] = $organisation;

        if ($organisation->getEventLines()->count() == 0) {
            return $this->render(
                'administration/organisation/event/new.html.twig', $arr + ["no_event_lines" => true]
            );
        }

        $arr["no_event_lines"] = false;

        $event = new Event();
        $newEventForm = $this->createForm(NewEventType::class, $event, ["organisation" => $organisation]);
        $newEventForm->handleRequest($request);

        if ($newEventForm->isSubmitted()) {
            if ($newEventForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $eventPast = EventPast::createFromEvent($event, EventChangeType::CREATED_BY_ADMIN);
                $em->persist($eventPast);
                $em->persist($event);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.event_add", [], "event"));
                $newEventForm = $this->createForm(NewEventType::class, new Event(), ["organisation" => $organisation]);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["new_event_form"] = $newEventForm->createView();
        return $this->render(
            'administration/organisation/event/new.html.twig', $arr
        );
    }

    /**
     * @Route("/{event}/edit", name="administration_organisation_event_edit")
     * @param Request $request
     * @param Organisation $organisation
     * @param Event $event
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $editEventForm = $this->createForm(NewEventType::class, $event, ["organisation" => $organisation]);
        $arr = [];

        $editEventForm->handleRequest($request);

        if ($editEventForm->isSubmitted()) {
            if ($editEventForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $eventPast = EventPast::createFromEvent($event, EventChangeType::CHANGED_BY_ADMIN);
                $em->persist($eventPast);
                $em->persist($event);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.event_save", [], "event"));
                $editEventForm = $this->createForm(NewEventType::class, $event, ["organisation" => $organisation]);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["edit_event_form"] = $editEventForm->createView();
        return $this->render(
            'administration/organisation/event/edit.html.twig', $arr
        );
    }

    /**
     * @Route("/{event}/remove", name="administration_organisation_event_remove")
     * @param Request $request
     * @param Organisation $organisation
     * @param Event $event
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $removeEventForm = $this->createForm(RemoveThingType::class, $event);
        $arr = [];

        $removeEventForm->handleRequest($request);

        if ($removeEventForm->isSubmitted()) {
            if ($removeEventForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $eventPast = EventPast::createFromEvent($event, EventChangeType::REMOVED_BY_ADMIN);
                $em->persist($eventPast);
                $em->remove($event);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.event_save", [], "event"));
                return $this->redirectToRoute("administration_organisation_events", ["organisation" => $organisation->getId()]);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["remove_event_form"] = $removeEventForm->createView();
        return $this->render(
            'administration/organisation/event/remove.html.twig', $arr
        );
    }


    /**
     * @Route("/import/download/template", name="administration_organisation_event_import_download_template")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function importDownloadTemplateAction(Request $request, Organisation $organisation)
    {
        $eventTrans = $this->get("translator")->trans("event", [], "event");
        $newEventForm = $this->createForm(NewEventType::class);
        $exchangeService = $this->get("app.exchange_service");

        return $this->renderCsv($eventTrans . ".csv", $exchangeService->getCsvHeader($newEventForm), []);
    }

    /**
     * @Route("/import", name="administration_organisation_event_import")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function importAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);

        $importEventsForm = $this->createForm(ImportFileType::class);
        $importFileModel = new ImportFileModel("/import");
        $importEventsForm->setData($importFileModel);

        $importEventsForm->handleRequest($request);

        if ($importEventsForm->isSubmitted()) {
            if ($importEventsForm->isValid()) {
                $newEventForm = $this->createForm(NewEventType::class);
                $exchangeService = $this->get("app.exchange_service");
                if ($exchangeService->importCsv($newEventForm, function () use ($organisation) {
                    $event = new Event();
                    $event->setOrganisation($organisation);
                    return $event;
                }, $importFileModel)
                ) {
                    $importEventsForm = $this->createForm(ImportFileType::class);
                    $this->displaySuccess($this->get("translator")->trans("success.import_successful", [], "import"));
                }
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr = [];
        $arr["import_events_form"] = $importEventsForm->createView();

        return $this->render(
            'administration/organisation/event/import.html.twig', $arr + ["organisation" => $organisation]
        );
    }
}