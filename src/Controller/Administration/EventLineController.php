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
use App\Entity\Member;
use App\Entity\Organisation;
use App\Enum\SubmitButtonType;
use App\Form\Event\ImportEventsType;
use App\Helper\DateTimeFormatter;
use App\Helper\StaticMessageHelper;
use App\Model\Form\ImportFileModel;
use App\Security\Voter\EventLineVoter;
use App\Security\Voter\OrganisationVoter;
use App\Service\ExchangeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/event_line")
 * @Security("has_role('ROLE_USER')")
 */
class EventLineController extends BaseController
{
    /**
     * @Route("/new", name="administration_event_line_new")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function newAction(Request $request, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $eventLine = new EventLine();
        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $eventLine,
            SubmitButtonType::CREATE,
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var Member $entity */
                return $this->redirectToRoute('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $entity->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['new_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/new.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_lines', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{eventLine}/edit", name="administration_event_line_edit")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, EventLine $eventLine, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::EDIT, $eventLine);

        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $eventLine,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var Member $entity */
                return $this->redirectToRoute('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $entity->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['edit_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/edit.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }

    /**
     * @Route("/{eventLine}/remove", name="administration_event_line_remove")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, EventLine $eventLine, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::REMOVE, $eventLine);

        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $eventLine,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var Member $entity */
                return $this->redirectToRoute('administration_organisation_event_lines', ['organisation' => $organisation->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['remove_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/remove.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }

    /**
     * @Route("/import/template", name="administration_event_line_import_template")
     *
     * @param Organisation $organisation
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function importDownloadTemplateAction(Organisation $organisation, TranslatorInterface $translator)
    {
        $eventTrans = $translator->trans('entity.name', [], 'entity_event');

        $firstMemberId = 1;
        foreach ($organisation->getMembers() as $member) {
            $firstMemberId = $member->getId();
        }

        return $this->renderCsv(
            $eventTrans . '.csv',
            [
                [
                    (new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT),
                    (new \DateTime('now + 1 day'))->format(DateTimeFormatter::DATE_TIME_FORMAT),
                    $firstMemberId,
                ],
            ],
            $this->getImportFileHeader($translator)
        );
    }

    /**
     * @param TranslatorInterface $translator
     *
     * @return string[]
     */
    private function getImportFileHeader(TranslatorInterface $translator)
    {
        $start = $translator->trans('start_date_time', [], 'entity_event');
        $end = $translator->trans('end_date_time', [], 'entity_event');
        $memberId = $translator->trans('member_id', [], 'entity_event');

        return [$start, $end, $memberId];
    }

    /**
     * @Route("/{eventLine}/import", name="administration_event_line_import")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param TranslatorInterface $translator
     * @param ExchangeService $exchangeService
     *
     * @return Response
     */
    public function importAction(Request $request, Organisation $organisation, EventLine $eventLine, TranslatorInterface $translator, ExchangeService $exchangeService)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);

        $importForm = $this->handleForm(
            $this->createForm(
                ImportEventsType::class
            ),
            $request,
            $translator,
            new ImportFileModel('/public/import'),
            function ($form, $importFileModel) use ($organisation, $eventLine, $translator, $exchangeService) {
                /* @var Form $form */
                /* @var ImportFileModel $importFileModel */
                $members = $this->getDoctrine()->getRepository('App:Member')->getIdAssociatedArray($organisation);
                if ($exchangeService->importCsvAdvanced(function ($data) use ($organisation, $eventLine, $members, $translator) {
                    $event = new Event();
                    $event->setStartDateTime(new \DateTime($data[0]));
                    $event->setEndDateTime(new \DateTime($data[1]));
                    if (isset($members[$data[2]])) {
                        $event->setMember($members[$data[2]]);
                    } else {
                        $this->get('session.flash_bag')->set(
                            StaticMessageHelper::FLASH_ERROR,
                            $translator->trans('error.file_upload_failed', [], 'import')
                        );
                    }
                    $event->setEventLine($eventLine);

                    return $event;
                }, function ($header) use ($organisation, $translator) {
                    $expectedHeader = $this->getImportFileHeader($translator);
                    for ($i = 0; $i < count($header); ++$i) {
                        if ($expectedHeader[$i] !== $header[$i]) {
                            $this->get('session.flash_bag')->set(
                                StaticMessageHelper::FLASH_ERROR,
                                $translator->trans('error.file_upload_failed', [], 'import')
                            );

                            return false;
                        }
                    }

                    return true;
                }, $importFileModel)
                ) {
                    return $this->redirectToRoute('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()]);
                }

                return $form;
            }
        );

        if ($importForm instanceof Response) {
            return $importForm;
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['import_form'] = $importForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/import.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_lines', ['organisation' => $organisation->getId()])
        );
    }
}
