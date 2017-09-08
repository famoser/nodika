<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 15:27
 */

namespace AppBundle\Controller\Administration\Organisation;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Form\Generic\ImportFileType;
use AppBundle\Form\Generic\RemoveThingType;
use AppBundle\Form\Member\NewMemberType;
use AppBundle\Model\Form\ImportFileModel;
use AppBundle\Security\Voter\MemberVoter;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/members")
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
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);

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
        $this->denyAccessUnlessGranted(MemberVoter::EDIT, $member);

        $editMemberForm = $this->createForm(NewMemberType::class);
        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["member"] = $member;

        $editMemberForm->setData($member);
        $editMemberForm->handleRequest($request);

        if ($editMemberForm->isSubmitted()) {
            if ($editMemberForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($member);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.member_save", [], "member"));
                $editMemberForm = $this->createForm(NewMemberType::class);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["edit_member_form"] = $editMemberForm->createView();
        return $this->render(
            'administration/organisation/member/edit.html.twig', $arr
        );
    }

    /**
     * @Route("/{member}/remove", name="administration_organisation_member_remove")
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, Member $member)
    {
        $this->denyAccessUnlessGranted(MemberVoter::REMOVE, $member);

        $removeMemberForm = $this->createForm(RemoveThingType::class);
        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["member"] = $member;

        $removeMemberForm->handleRequest($request);

        if ($removeMemberForm->isSubmitted()) {
            if ($removeMemberForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($member);
                $em->flush();

                $this->displaySuccess($this->get("translator")->trans("successful.member_save", [], "member"));
                return $this->redirectToRoute("administration_organisation_members", ["organisation" => $organisation->getId()]);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["remove_member_form"] = $removeMemberForm->createView();
        return $this->render(
            'administration/organisation/member/remove.html.twig', $arr
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
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);

        $importMembersForm = $this->createForm(ImportFileType::class);
        $importFileModel = new ImportFileModel("/img/import");
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