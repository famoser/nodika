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
use App\Helper\HashHelper;
use App\Model\Form\ImportFileModel;
use App\Security\Voter\MemberVoter;
use App\Security\Voter\OrganisationVoter;
use App\Service\EmailService;
use App\Service\ExchangeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/members")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseController
{
    /**
     * @Route("/new", name="administration_member_new")
     *
     * @param Request $request
     * @param Organisation $organisation
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
     * @Route("/invite/all", name="administration_member_invite_all")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function inviteAllAction(Request $request, Organisation $organisation, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        if ('' === $organisationSetting->getMemberInviteEmailSubject()) {
            $organisationSetting->setMemberInviteEmailSubject(
                $translator->trans('members_invite.email.default_subject', [], 'administration_organisation')
            );
        }
        if ('' === $organisationSetting->getMemberInviteEmailMessage()) {
            $organisationSetting->setMemberInviteEmailMessage(
                $translator->trans('members_invite.email.default_message', [], 'administration_organisation')
            );
        }

        $hasPendingMember = false;
        foreach ($organisation->getMembers() as $member) {
            if (null === $member->getInvitationDateTime()) {
                $hasPendingMember = true;
            }
        }

        if ('POST' === $request->getMethod()) {
            $canForward = $hasPendingMember;
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'subject')) {
                    $organisationSetting->setMemberInviteEmailSubject($value);
                } elseif ('message' === $key) {
                    $organisationSetting->setMemberInviteEmailMessage($value);
                    if (!mb_strpos($value, 'LINK_REPLACE')) {
                        $translator->trans('members_invite.error.no_link_replace_in_message', [], 'administration_organisation');
                        $canForward = false;
                    }
                }
            }
            $this->fastSave($organisationSetting);

            if ($canForward) {
                return $this->redirectToRoute('administration_organisation_members_invite_preview', ['organisation' => $organisation->getId()]);
            }
        }

        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/members_invite.html.twig',
            $arr +
            [
                'members' => $organisation->getMembers(),
                'subject' => $organisationSetting->getMemberInviteEmailSubject(),
                'message' => $organisationSetting->getMemberInviteEmailMessage(),
                'hasPendingMember' => $hasPendingMember,
            ],
            $this->generateUrl('administration_organisation_members', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/invite/all/preview", name="administration_member_invite_all_preview")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     *
     * @return Response
     */
    public function membersInvitePreviewAction(Request $request, Organisation $organisation, TranslatorInterface $translator, EmailService $emailService)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        /* @var Member[] $notInvitedMembers */
        $notInvitedMembers = [];

        foreach ($organisation->getMembers() as $member) {
            if (null === $member->getInvitationDateTime()) {
                $notInvitedMembers[] = $member;
            }
        }

        if ('POST' === $request->getMethod()) {
            $variableMapping = [];
            foreach ($notInvitedMembers as $member) {
                $variableMapping[$member->getId()] = [];
                $variableMapping[$member->getId()]['FREE_1_REPLACE'] = '';
                $variableMapping[$member->getId()]['FREE_2_REPLACE'] = '';
                $variableMapping[$member->getId()]['FREE_3_REPLACE'] = '';
            }
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'free_1_')) {
                    $memberId = (int)mb_substr($key, 7); //to cut off free_1_
                    $variableMapping[$memberId]['FREE_1_REPLACE'] = $value;
                } elseif (0 === mb_strpos($key, 'free_2_')) {
                    $memberId = (int)mb_substr($key, 7); //to cut off free_2_
                    $variableMapping[$memberId]['FREE_2_REPLACE'] = $value;
                } elseif (0 === mb_strpos($key, 'free_3_')) {
                    $memberId = (int)mb_substr($key, 7); //to cut off free_3_
                    $variableMapping[$memberId]['FREE_3_REPLACE'] = $value;
                }
            }

            foreach ($notInvitedMembers as $member) {
                $subject = $organisationSetting->getMemberInviteEmailSubject();
                $body = $organisationSetting->getMemberInviteEmailMessage();

                $member->setInvitationHash(HashHelper::createNewResetHash());
                $member->setInvitationDateTime(new \DateTime());

                $variableMapping[$member->getId()]['LINK_REPLACE'] =
                    $this->generateUrl('access_invite', ['invitationHash' => $member->getInvitationHash()], UrlGeneratorInterface::ABSOLUTE_URL);
                $variableMapping[$member->getId()]['MEMBER_NAME_REPLACE'] = $member->getName();

                foreach ($variableMapping[$member->getId()] as $search => $replace) {
                    $subject = str_replace($search, $replace, $subject);
                    $body = str_replace($search, $replace, $body);
                }

                $emailService->sendPlainEmail($member->getEmail(), $subject, $body);

                $this->fastSave($member);
            }

            $this->displaySuccess($translator->trans('members_invite.successful.emails_send', ['%count%' => count($notInvitedMembers)], 'administration_organisation'));

            return $this->redirectToRoute('administration_organisation_members', ['organisation' => $organisation->getId()]);
        }

        $arr = [];

        $showFree1 =
            mb_strpos($organisationSetting->getMemberInviteEmailSubject(), 'FREE_1_REPLACE') ||
            mb_strpos($organisationSetting->getMemberInviteEmailMessage(), 'FREE_1_REPLACE');

        $showFree2 =
            mb_strpos($organisationSetting->getMemberInviteEmailSubject(), 'FREE_2_REPLACE') ||
            mb_strpos($organisationSetting->getMemberInviteEmailMessage(), 'FREE_2_REPLACE');

        $showFree3 =
            mb_strpos($organisationSetting->getMemberInviteEmailSubject(), 'FREE_3_REPLACE') ||
            mb_strpos($organisationSetting->getMemberInviteEmailMessage(), 'FREE_3_REPLACE');

        $arr['showFree1'] = $showFree1;
        $arr['showFree2'] = $showFree2;
        $arr['showFree3'] = $showFree3;
        $arr['organisation'] = $organisation;
        $arr['members'] = $notInvitedMembers;
        $arr['subject'] = $organisationSetting->getMemberInviteEmailSubject();
        $arr['message'] = $organisationSetting->getMemberInviteEmailMessage();

        return $this->renderWithBackUrl(
            'administration/organisation/members_invite_preview.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_members_invite', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{member}/edit", name="administration_member_edit")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
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
     * @Route("/{member}/invite", name="administration_member_invite")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function inviteAction(Request $request, Organisation $organisation, Member $member, TranslatorInterface $translator)
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
     * @Route("/import/template", name="administration_member_import_template")
     *
     * @param TranslatorInterface $translator
     * @param ExchangeService $exchangeService
     *
     * @return Response
     */
    public function importDownloadTemplateAction(TranslatorInterface $translator, ExchangeService $exchangeService)
    {
        $memberTrans = $translator->trans('entity.name', [], 'entity_member');
        $newMemberForm = $this->createForm(MemberType::class);

        return $this->renderCsv($memberTrans . '.csv', [], $exchangeService->getCsvHeader($newMemberForm));
    }

    /**
     * @Route("/import", name="administration_member_import")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param TranslatorInterface $translator
     * @param ExchangeService $exchangeService
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
