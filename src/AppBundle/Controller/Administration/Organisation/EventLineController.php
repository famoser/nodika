<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration\Organisation;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\Event\ImportEventsType;
use AppBundle\Form\EventLine\EventLineType;
use AppBundle\Form\Generic\RemoveThingType;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Model\Event\ImportEventModel;
use AppBundle\Security\Voter\EventLineVoter;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
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
        $myForm = $this->handleCrudForm(
            $request,
            $eventLine,
            SubmitButtonType::CREATE,
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var Member $entity */
                return $this->redirectToRoute("administration_organisation_event_line_administer", ["organisation" => $organisation->getId(), "eventLine" => $entity->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["new_form"] = $myForm->createView();
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

        $myForm = $this->handleCrudForm(
            $request,
            $eventLine,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var Member $entity */
                return $this->redirectToRoute("administration_organisation_event_line_administer", ["organisation" => $organisation->getId(), "eventLine" => $entity->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["edit_form"] = $myForm->createView();
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


        $myForm = $this->handleCrudForm(
            $request,
            $eventLine,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var Member $entity */
                return $this->redirectToRoute("administration_organisation_event_lines", ["organisation" => $organisation->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["remove_form"] = $myForm->createView();
        $arr["event_line"] = $eventLine;
        return $this->render(
            'administration/organisation/event_line/remove.html.twig', $arr
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
     * @Route("/import/download/template", name="administration_organisation_event_line_import_download_template")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function importDownloadTemplateAction(Request $request, Organisation $organisation)
    {
        $eventTrans = $this->get("translator")->trans("event", [], "event");

        return $this->renderCsv(
            $eventTrans . ".csv",
            $this->getImportFileHeader(),
            [$this->getImportFileExampleLine()]
        );
    }

    /**
     * @return string[]
     */
    private function getImportFileHeader()
    {
        $start = $this->get("translator")->trans("start_date_time", [], "event");
        $end = $this->get("translator")->trans("end_date_time", [], "event");
        $memberId = $this->get("translator")->trans("member_id", [], "event");
        return [$start, $end, $memberId];
    }

    /**
     * @return string[]
     */
    private function getImportFileExampleLine()
    {
        $nowExample = new \DateTime();
        $endExample = new \DateTime("now + 1 day");
        return [$nowExample->format(DateTimeFormatter::DATE_TIME_FORMAT), $endExample->format(DateTimeFormatter::DATE_TIME_FORMAT), 1];
    }

    /**
     * @Route("/import", name="administration_organisation_event_line_import")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function importAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);

        $members = $this->getDoctrine()->getRepository("AppBundle:Member")->getIdAssociatedArray($organisation);

        $arr = [];
        $arr["members"] = $members;

        $importFileModel = new ImportEventModel("/img/import");
        $importEventsForm = $this->createForm(ImportEventsType::class, $importFileModel, ["organisation" => $organisation]);
        $importEventsForm->handleRequest($request);

        if ($importEventsForm->isSubmitted()) {
            if ($importEventsForm->isValid()) {
                $exchangeService = $this->get("app.exchange_service");
                $eventLine = $importFileModel->getEventLine();
                if ($exchangeService->importCsvAdvanced(function ($data) use ($organisation, $members, $eventLine) {
                    $event = new Event();
                    $event->setStartDateTime(new \DateTime($data[0]));
                    $event->setEndDateTime(new \DateTime($data[1]));
                    if (isset($members[$data[2]])) {
                        $event->setMember($members[$data[2]]);
                    } else {
                        return null;
                    }
                    $event->setEventLine($eventLine);
                    return $event;
                }, function ($header) use ($organisation) {
                    $expectedHeader = $this->getImportFileHeader();
                    for ($i = 0; $i < count($header); $i++) {
                        if ($expectedHeader[$i] != $header[$i]) {
                            $this->get("session.flash_bag")->set(StaticMessageHelper::FLASH_ERROR, $this->get("translator")->trans("error.file_upload_failed", [], "import"));
                            return false;
                        }
                    }
                    return true;
                }, $importFileModel)
                ) {
                    $importEventsForm = $this->createForm(ImportEventsType::class, $importFileModel, ["organisation" => $organisation]);
                    $this->displaySuccess($this->get("translator")->trans("success.import_successful", [], "import"));
                }
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["import_events_form"] = $importEventsForm->createView();

        return $this->render(
            'administration/organisation/event/import.html.twig', $arr + ["organisation" => $organisation]
        );
    }
}