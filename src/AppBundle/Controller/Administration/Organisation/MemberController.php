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
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\Generic\ImportFileType;
use AppBundle\Form\Member\MemberType;
use AppBundle\Model\Form\ImportFileModel;
use AppBundle\Security\Voter\MemberVoter;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
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

        $member = new Member();
        $member->setOrganisation($organisation);
        $myForm = $this->handleCrudForm(
            $request,
            $member,
            SubmitButtonType::CREATE,
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var Member $entity */
                //return $this->redirectToRoute("administration_organisation_member_administer", ["organisation" => $organisation->getId(), "member" => $entity->getId()]);
                return $this->redirectToRoute("administration_organisation_member_new", ["organisation" => $organisation->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["new_form"] = $myForm->createView();
        return $this->render(
            'administration/organisation/member/new.html.twig', $arr
        );
    }

    /**
     * @Route("/{member}/administer", name="administration_organisation_member_administer")
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @return Response
     */
    public function administerAction(Request $request, Organisation $organisation, Member $member)
    {
        $this->denyAccessUnlessGranted(MemberVoter::ADMINISTRATE, $member);

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["member"] = $member;

        return $this->render(
            'administration/organisation/member/administer.html.twig', $arr
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

        $myForm = $this->handleCrudForm(
            $request,
            $member,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation) {
                /* @var Member $entity */
                /* @var Form $form */
                return $this->redirectToRoute("administration_organisation_member_administer", ["organisation" => $organisation->getId(), "member" => $entity->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["member"] = $member;
        $arr["edit_form"] = $myForm->createView();
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

        $myForm = $this->handleCrudForm(
            $request,
            $member,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation) {
                /* @var Member $entity */
                /* @var Form $form */
                return $this->redirectToRoute("administration_organisation_members", ["organisation" => $organisation->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["member"] = $member;
        $arr["remove_form"] = $myForm->createView();
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
        $memberTrans = $this->get("translator")->trans("entity.name", [], "entity_member");
        $newMemberForm = $this->createForm(MemberType::class);
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


        $importForm = $this->handleForm(
            $this->createForm(ImportFileType::class),
            $request,
            new ImportFileModel("/img/import"),
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var ImportFileModel $entity */
                $newMemberForm = $this->createForm(MemberType::class);
                $exchangeService = $this->get("app.exchange_service");
                if ($exchangeService->importCsv($newMemberForm, function () use ($organisation) {
                    $member = new Member();
                    $member->setOrganisation($organisation);
                    return $member;
                }, $entity)
                ) {
                    $this->displaySuccess($this->get("translator")->trans("success.import_successful", [], "import"));
                    return $this->createForm(ImportFileType::class);
                }
                return $form;
            }
        );

        if ($importForm instanceof Response) {
            return $importForm;
        }

        $arr = [];
        $arr["import_form"] = $importForm->createView();

        return $this->render(
            'administration/organisation/member/import.html.twig', $arr + ["organisation" => $organisation]
        );
    }
}