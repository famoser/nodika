<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Organisation;

use App\Controller\Base\BaseController;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Enum\SubmitButtonType;
use App\Form\Member\ImportMembersType;
use App\Form\Member\MemberType;
use App\Model\Form\ImportFileModel;
use App\Security\Voter\MemberVoter;
use App\Security\Voter\OrganisationVoter;
use App\Service\ExchangeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/members")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_member_new")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);

        $member = new Member();
        $member->setOrganisation($organisation);
        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $member,
            SubmitButtonType::CREATE,
            function ($form, $entity) use ($organisation) {
                /* @var Form $form */
                /* @var Member $entity */
                return $this->redirectToRoute('administration_organisation_member_new', ['organisation' => $organisation->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['new_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/member/new.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_members', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{member}/administer", name="administration_organisation_member_administer")
     *
     * @param Organisation        $organisation
     * @param Member              $member
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function administerAction(Organisation $organisation, Member $member, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(MemberVoter::ADMINISTRATE, $member);

        //show message to add itself to member
        $isPartOfOrganisation = false;
        foreach ($this->getPerson()->getMembers() as $itMember) {
            $isPartOfOrganisation =
                $isPartOfOrganisation ||
                $itMember->getOrganisation()->getId() === $organisation->getId();
        }
        if (!$isPartOfOrganisation) {
            $this->displayInfo(
                $translator->trans('administer.not_part_of_organisation_yet', [], 'administration_organisation_member'),
                $this->generateUrl('administration_organisation_member_add_self', ['organisation' => $organisation->getId(), 'member' => $member->getId()])
            );
        }

        $arr['organisation'] = $organisation;
        $arr['member'] = $member;

        return $this->renderWithBackUrl(
            'administration/organisation/member/administer.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_members', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{member}/add_self", name="administration_organisation_member_add_self")
     *
     * @param Organisation $organisation
     * @param Member       $member
     *
     * @return Response
     */
    public function addSelfAction(Organisation $organisation, Member $member)
    {
        $this->denyAccessUnlessGranted(MemberVoter::ADMINISTRATE, $member);

        $this->getPerson()->addMember($member);
        $this->fastSave($this->getPerson());

        return $this->redirectToRoute('administration_organisation_member_administer', ['organisation' => $organisation->getId(), 'member' => $member->getId()]);
    }

    /**
     * @Route("/{member}/edit", name="administration_organisation_member_edit")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param Member              $member
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, Member $member, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(MemberVoter::EDIT, $member);

        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $member,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation) {
                /* @var Member $entity */
                /* @var Form $form */
                return $this->redirectToRoute('administration_organisation_member_administer', ['organisation' => $organisation->getId(), 'member' => $entity->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['member'] = $member;
        $arr['edit_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/member/edit.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_member_administer', ['organisation' => $organisation->getId(), 'member' => $member->getId()])
        );
    }

    /**
     * @Route("/{member}/remove", name="administration_organisation_member_remove")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param Member              $member
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, Member $member, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(MemberVoter::REMOVE, $member);

        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $member,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation) {
                /* @var Member $entity */
                /* @var Form $form */
                return $this->redirectToRoute('administration_organisation_members', ['organisation' => $organisation->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['member'] = $member;
        $arr['remove_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/member/remove.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_member_administer', ['organisation' => $organisation->getId(), 'member' => $member->getId()])
        );
    }

    /**
     * @Route("/import/download/template", name="administration_organisation_member_import_download_template")
     *
     * @param TranslatorInterface $translator
     * @param ExchangeService     $exchangeService
     *
     * @return Response
     */
    public function importDownloadTemplateAction(TranslatorInterface $translator, ExchangeService $exchangeService)
    {
        $memberTrans = $translator->trans('entity.name', [], 'entity_member');
        $newMemberForm = $this->createForm(MemberType::class);

        return $this->renderCsv($memberTrans.'.csv', [], $exchangeService->getCsvHeader($newMemberForm));
    }

    /**
     * @Route("/import", name="administration_organisation_member_import")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param TranslatorInterface $translator
     * @param ExchangeService     $exchangeService
     *
     * @return Response
     */
    public function importAction(Request $request, Organisation $organisation, TranslatorInterface $translator, ExchangeService $exchangeService)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::EDIT, $organisation);

        $importForm = $this->handleForm(
            $this->createForm(
                ImportMembersType::class
            ),
            $request,
            $translator,
            new ImportFileModel('/img/import'),
            function ($form, $entity) use ($organisation, $exchangeService) {
                /* @var Form $form */
                /* @var ImportFileModel $entity */
                $newMemberForm = $this->createForm(MemberType::class);
                if ($exchangeService->importCsv($newMemberForm, function () use ($organisation) {
                    $member = new Member();
                    $member->setOrganisation($organisation);

                    return $member;
                }, $entity)
                ) {
                    return $this->redirectToRoute('administration_organisation_members', ['organisation' => $organisation->getId()]);
                }

                return $form;
            }
        );

        if ($importForm instanceof Response) {
            return $importForm;
        }

        $arr = [];
        $arr['import_form'] = $importForm->createView();
        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/member/import.html.twig',
            $arr + ['organisation' => $organisation],
            $this->generateUrl('administration_organisation_members', ['organisation' => $organisation->getId()])
        );
    }
}
