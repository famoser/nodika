<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 15:27
 */

namespace AppBundle\Controller\Administration;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Form\ImportFileType;
use AppBundle\Form\Member\ImportMembersType;
use AppBundle\Form\Member\NewMemberType;
use AppBundle\Helper\FlashMessageHelper;
use AppBundle\Model\Form\ImportFileModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/organisation/{organisation}/members")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_member_new")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation)
    {
        $newMemberForm = $this->createForm(NewMemberType::class);
        $arr = [];

        $member = new Member();
        $newMemberForm->setData($member);
        $newMemberForm->handleRequest($request);

        if ($newMemberForm->isSubmitted()) {
            if ($newMemberForm->isValid()) {
                $member->setOrganisation($organisation);
                $em = $this->getDoctrine()->getManager();
                $em->persist($member);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.member_add", [], "member"));
                $newMemberForm = $this->createForm(NewMemberType::class);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["new_member_form"] = $newMemberForm->createView();
        return $this->render(
            'administration/organisation/member/new.html.twig', $arr
        );
    }

    /**
     * @Route("/{member}/edit", name="administration_organisation_member_edit")
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, Member $member)
    {
        $newMemberForm = $this->createForm(NewMemberType::class);
        $arr = [];

        $newMemberForm->setData($member);
        $newMemberForm->handleRequest($request);

        if ($newMemberForm->isSubmitted()) {
            if ($newMemberForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($member);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.member_save", [], "member"));
                $newMemberForm = $this->createForm(NewMemberType::class);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["edit_member_form"] = $newMemberForm->createView();
        return $this->render(
            'administration/organisation/member/edit.html.twig', $arr
        );
    }


    /**
     * @Route("/import/download/template", name="administration_organisation_member_import_download_template")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function importDownloadTemplateAction(Request $request, Organisation $organisation)
    {
        $memberTrans = $this->get("translator")->trans("member", [], "member");
        $newMemberForm = $this->createForm(NewMemberType::class);
        $exchangeService = $this->get("app.exchange_service");

        return $this->renderCsv($memberTrans . ".csv", $exchangeService->getCsvHeader($newMemberForm), []);
    }


    /**
     * @Route("/import", name="administration_organisation_member_import")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function importAction(Request $request, Organisation $organisation)
    {
        $importMembersForm = $this->createForm(ImportFileType::class);
        $importFileModel = new ImportFileModel("/import");
        $importMembersForm->setData($importFileModel);

        $importMembersForm->handleRequest($request);

        if ($importMembersForm->isSubmitted()) {
            if ($importMembersForm->isValid()) {
                $newMemberForm = $this->createForm(NewMemberType::class);
                $exchangeService = $this->get("app.exchange_service");
                if ($exchangeService->importCsv($newMemberForm, function () use ($organisation) {
                    $member = new Member();
                    $member->setOrganisation($organisation);
                    return $member;
                }, $importFileModel)
                ) {
                    $importMembersForm = $this->createForm(ImportFileType::class);
                    $this->displaySuccess($this->get("translator")->trans("success.import_successful", [], "import"));
                }
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr = [];
        $arr["import_members_form"] = $importMembersForm->createView();

        return $this->render(
            'administration/organisation/member/import.html.twig', $arr + ["organisation" => $organisation]
        );
    }
}