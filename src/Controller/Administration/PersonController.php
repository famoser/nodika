<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Organisation\Member;

use App\Controller\Base\BaseController;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Enum\SubmitButtonType;
use App\Security\Voter\MemberVoter;
use App\Security\Voter\PersonVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/persons")
 * @Security("has_role('ROLE_USER')")
 */
class PersonController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_member_person_new")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation, Member $member, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(MemberVoter::EDIT, $organisation);

        $person = new Person();
        $person->addMember($member);
        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $person,
            SubmitButtonType::CREATE,
            function ($form, $entity) use ($organisation, $member) {
                /* @var Form $form */
                /* @var Member $entity */
                //return $this->redirectToRoute("administration_organisation_member_administer", ["organisation" => $organisation->getId(), "member" => $entity->getId()]);
                return $this->redirectToRoute('administration_organisation_member_person_new', ['organisation' => $organisation->getId(), 'member' => $member->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['member'] = $member;
        $arr['new_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/member/person/new.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_member_administer', ['organisation' => $organisation->getId(), 'member' => $member->getId()])
        );
    }

    /**
     * @Route("/{person}/edit", name="administration_organisation_member_person_edit")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @param Person $person
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, Member $member, Person $person, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(PersonVoter::EDIT, $member);

        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $person,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation, $member) {
                /* @var Member $entity */
                /* @var Form $form */
                return $this->redirectToRoute('administration_organisation_member_administer', ['organisation' => $organisation->getId(), 'member' => $member->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['member'] = $member;
        $arr['person'] = $person;
        $arr['edit_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/member/person/edit.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_member_administer', ['organisation' => $organisation->getId(), 'member' => $member->getId()])
        );
    }

    /**
     * @Route("/{person}/remove", name="administration_organisation_member_person_remove")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @param Person $person
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, Member $member, Person $person, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(PersonVoter::REMOVE, $member);

        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $person,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation, $member) {
                /* @var Member $entity */
                /* @var Form $form */
                return $this->redirectToRoute('administration_organisation_member_administer', ['organisation' => $organisation->getId(), 'member' => $member->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['organisation'] = $organisation;
        $arr['member'] = $member;
        $arr['person'] = $person;
        $arr['remove_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/member/person/remove.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_member_person_edit', ['organisation' => $organisation->getId(), 'member' => $member->getId(), 'person' => $person->getId()])
        );
    }



    /**
     * @Route("/{organisation}/persons/invite", name="administration_organisation_persons_invite")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function personsInviteAction(Request $request, Organisation $organisation, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        if ('' === $organisationSetting->getPersonInviteEmailSubject()) {
            $organisationSetting->setPersonInviteEmailSubject(
                $translator->trans('persons_invite.email.default_subject', [], 'administration_organisation')
            );
        }
        if ('' === $organisationSetting->getPersonInviteEmailMessage()) {
            $organisationSetting->setPersonInviteEmailMessage(
                $translator->trans('persons_invite.email.default_message', [], 'administration_organisation')
            );
        }

        $hasPendingPerson = false;
        $persons = [];
        foreach ($organisation->getMembers() as $member) {
            foreach ($member->getPersons() as $person) {
                if (null === $person->getFrontendUser()) {
                    $hasPendingPerson = true;
                }
                $persons[$person->getId()] = $person;
            }
        }

        if ('POST' === $request->getMethod()) {
            $canForward = $hasPendingPerson;
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'subject')) {
                    $organisationSetting->setPersonInviteEmailSubject($value);
                } elseif ('message' === $key) {
                    $organisationSetting->setPersonInviteEmailMessage($value);
                    if (!mb_strpos($value, 'LINK_REPLACE')) {
                        $translator->trans('persons_invite.error.no_link_replace_in_message', [], 'administration_organisation');
                        $canForward = false;
                    }
                }
            }
            $this->fastSave($organisationSetting);

            if ($canForward) {
                return $this->redirectToRoute('administration_organisation_persons_invite_preview', ['organisation' => $organisation->getId()]);
            }
        }

        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/persons_invite.html.twig',
            $arr +
            [
                'persons' => $persons,
                'subject' => $organisationSetting->getPersonInviteEmailSubject(),
                'message' => $organisationSetting->getPersonInviteEmailMessage(),
                'hasPendingPerson' => $hasPendingPerson,
            ],
            $this->generateUrl('administration_organisation_members', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/persons/invite/preview", name="administration_organisation_persons_invite_preview")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     *
     * @return Response
     */
    public function personsInvitePreviewAction(Request $request, Organisation $organisation, TranslatorInterface $translator, EmailService $emailService)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        /* @var Person[] $notInvitedPersons */
        $notInvitedPersons = [];
        foreach ($organisation->getMembers() as $member) {
            foreach ($member->getPersons() as $person) {
                if (!$person->getHasBeenInvited() && null === $person->getFrontendUser()) {
                    $notInvitedPersons[$person->getId()] = $person;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $variableMapping = [];
            foreach ($notInvitedPersons as $person) {
                $variableMapping[$person->getId()] = [];
                $variableMapping[$person->getId()]['FREE_1_REPLACE'] = '';
                $variableMapping[$person->getId()]['FREE_2_REPLACE'] = '';
                $variableMapping[$person->getId()]['FREE_3_REPLACE'] = '';
            }
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'free_1_')) {
                    $personId = (int)mb_substr($key, 7); //to cut off free_1_
                    $variableMapping[$personId]['FREE_1_REPLACE'] = $value;
                } elseif (0 === mb_strpos($key, 'free_2_')) {
                    $personId = (int)mb_substr($key, 7); //to cut off free_2_
                    $variableMapping[$personId]['FREE_2_REPLACE'] = $value;
                } elseif (0 === mb_strpos($key, 'free_3_')) {
                    $personId = (int)mb_substr($key, 7); //to cut off free_3_
                    $variableMapping[$personId]['FREE_3_REPLACE'] = $value;
                }
            }

            foreach ($notInvitedPersons as $person) {
                $subject = $organisationSetting->getPersonInviteEmailSubject();
                $body = $organisationSetting->getPersonInviteEmailMessage();

                $person->setInvitationHash(HashHelper::createNewResetHash());
                $person->setInvitationDateTime(new \DateTime());

                $variableMapping[$person->getId()]['LINK_REPLACE'] =
                    $this->generateUrl('access_invite_person', ['invitationHash' => $person->getInvitationHash()], UrlGeneratorInterface::ABSOLUTE_URL);
                $variableMapping[$person->getId()]['PERSON_NAME_REPLACE'] = $person->getFullName();

                foreach ($variableMapping[$person->getId()] as $search => $replace) {
                    $subject = str_replace($search, $replace, $subject);
                    $body = str_replace($search, $replace, $body);
                }

                $emailService->sendPlainEmail($person->getEmail(), $subject, $body);
                $this->fastSave($person);
            }

            $this->displaySuccess($translator->trans('persons_invite.successful.emails_send', ['%count%' => count($notInvitedPersons)], 'administration_organisation'));

            return $this->redirectToRoute('administration_organisation_members', ['organisation' => $organisation->getId()]);
        }

        $arr = [];

        $showFree1 =
            mb_strpos($organisationSetting->getPersonInviteEmailSubject(), 'FREE_1_REPLACE') ||
            mb_strpos($organisationSetting->getPersonInviteEmailMessage(), 'FREE_1_REPLACE');

        $showFree2 =
            mb_strpos($organisationSetting->getPersonInviteEmailSubject(), 'FREE_2_REPLACE') ||
            mb_strpos($organisationSetting->getPersonInviteEmailMessage(), 'FREE_2_REPLACE');

        $showFree3 =
            mb_strpos($organisationSetting->getPersonInviteEmailSubject(), 'FREE_3_REPLACE') ||
            mb_strpos($organisationSetting->getPersonInviteEmailMessage(), 'FREE_3_REPLACE');

        $arr['showFree1'] = $showFree1;
        $arr['showFree2'] = $showFree2;
        $arr['showFree3'] = $showFree3;
        $arr['organisation'] = $organisation;
        $arr['persons'] = $notInvitedPersons;
        $arr['subject'] = $organisationSetting->getPersonInviteEmailSubject();
        $arr['message'] = $organisationSetting->getPersonInviteEmailMessage();

        return $this->renderWithBackUrl(
            'administration/organisation/persons_invite_preview.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_persons_invite', ['organisation' => $organisation->getId()])
        );
    }
}
