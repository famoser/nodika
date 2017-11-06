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
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Model\Form\ImportFileModel;
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

        $arr["organisation"] = $organisation;
        $arr["new_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'administration/organisation/event_line/new.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_event_lines", ["organisation" => $organisation->getId()])
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

        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        $arr["edit_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'administration/organisation/event_line/edit.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_event_line_administer", ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()])
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

        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        $arr["remove_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'administration/organisation/event_line/remove.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_event_line_administer", ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()])
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

        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        return $this->renderWithBackUrl(
            'administration/organisation/event_line/administer.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_event_lines", ["organisation" => $organisation->getId()])
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
        $eventTrans = $this->get("translator")->trans("entity.name", [], "entity_event");

        $firstMemberId = 1;
        foreach ($organisation->getMembers() as $member) {
            $firstMemberId = $member->getId();
        }

        return $this->renderCsv(
            $eventTrans . ".csv",
            [
                [
                    (new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT),
                    (new \DateTime("now + 1 day"))->format(DateTimeFormatter::DATE_TIME_FORMAT),
                    $firstMemberId
                ]
            ],
            $this->getImportFileHeader()
        );
    }

    /**
     * @return string[]
     */
    private function getImportFileHeader()
    {
        $start = $this->get("translator")->trans("start_date_time", [], "entity_event");
        $end = $this->get("translator")->trans("end_date_time", [], "entity_event");
        $memberId = $this->get("translator")->trans("member_id", [], "entity_event");
        return [$start, $end, $memberId];
    }

    /**
     * @Route("/{eventLine}/import", name="administration_organisation_event_line_import")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function importAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);

        $importForm = $this->handleForm(
            $this->createForm(ImportEventsType::class),
            $request,
            new ImportFileModel("/public/import"),
            function ($form, $importFileModel) use ($organisation, $eventLine) {
                /* @var Form $form */
                /* @var ImportFileModel $importFileModel */
                $exchangeService = $this->get("app.exchange_service");
                $members = $this->getDoctrine()->getRepository("AppBundle:Member")->getIdAssociatedArray($organisation);
                if ($exchangeService->importCsvAdvanced(function ($data) use ($organisation, $eventLine, $members) {
                    $event = new Event();
                    $event->setStartDateTime(new \DateTime($data[0]));
                    $event->setEndDateTime(new \DateTime($data[1]));
                    if (isset($members[$data[2]])) {
                        $event->setMember($members[$data[2]]);
                    } else {
                        $this->get("session.flash_bag")->set(StaticMessageHelper::FLASH_ERROR, $this->get("translator")->trans("error.file_upload_failed", [], "import"));
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
                    return $this->redirectToRoute("administration_organisation_event_line_administer", ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()]);
                }
                return $form;
            }
        );

        if ($importForm instanceof Response) {
            return $importForm;
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        $arr["import_form"] = $importForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/import.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_event_lines", ["organisation" => $organisation->getId()])
        );
    }
}