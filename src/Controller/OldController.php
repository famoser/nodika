<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/1/18
 * Time: 4:36 PM
 */

namespace App\Controller;


class OldController
{
    /**
     * @Route("/register", name="access_register")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     *
     * @return FormInterface|Response
     */
    public function registerAction(Request $request, TranslatorInterface $translator, EmailService $emailService)
    {
        $organisation = Organisation::createFromPerson($this->getPerson());
        $organisation->setActiveEnd(new \DateTime('today + 31 days'));
        $organisation->setIsActive(true);
        $organisation->addLeader($this->getPerson());
        $newOrganisationForm = $this->handleFormDoctrinePersist(
            $this->createCrudForm(OrganisationType::class, SubmitButtonType::CREATE),
            $request,
            $translator,
            $organisation,
            function ($form, $entity) use ($organisation) {
                return $this->redirectToRoute('administration_organisation_setup', ['organisation' => $organisation->getId()]);
            }
        );

        if ($newOrganisationForm instanceof Response) {
            return $newOrganisationForm;
        }

        $arr['new_organisation_form'] = $newOrganisationForm->createView();


        $registerForm = $this->handleFormDoctrinePersist(
            $this->createCrudForm(PersonType::class, SubmitButtonType::REGISTER),
            $request,
            $translator,
            new Person(),
            function ($form, $person) use ($translator, $emailService) {
                /* @var Person $person */
                $existingUser = $this->getDoctrine()->getRepository('App:FrontendUser')->findOneBy(['email' => $person->getEmail()]);
                if (null !== $existingUser) {
                    $this->displayError($translator->trans('error.email_already_registered', [], 'access'));

                    return $form;
                }
                $user = FrontendUser::createFromPerson($person);
                $this->fastSave($person, $user);

                $subject = $translator->trans('register.subject', [], 'email_access');
                $receiver = $person->getEmail();
                $body = $translator->trans('register.message', [], 'email_access');
                $actionText = $translator->trans('register.action_text', [], 'email_access');
                $actionLink = $this->generateUrl(
                    'access_register_confirm',
                    ['confirmationToken' => $user->getResetHash()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $emailService->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink);

                return $this->redirectToRoute('access_register_thanks');
            }
        );

        if ($registerForm instanceof RedirectResponse) {
            return $registerForm;
        }

        $arr['register_form'] = $registerForm->createView();

        return $this->renderWithBackUrl(
            'access/register.html.twig',
            $arr,
            $this->generateUrl('access_login')
        );
    }

    /**
     * @Route("/invite/resend", name="access_invite_resend")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     *
     * @return FormInterface|Response
     */
    public function inviteResendAction(Request $request, TranslatorInterface $translator, EmailService $emailService)
    {
        if ('POST' === $request->getMethod()) {
            $email = $request->get('email');

            $person = $this->getDoctrine()->getRepository('App:Person')->findOneBy(['email' => $email]);
            if (null !== $person) {
                if (null === $person->getFrontendUser()) {
                    if ($person->getHasBeenInvited()) {
                        //resend invite email
                        $subject = $translator->trans('resend_invitation.subject', [], 'email_access');
                        $body = $translator->trans('resend_invitation.message', [], 'email_access');
                        $actionText = $translator->trans('resend_invitation.action_text', [], 'email_access');
                        $actionLink = $this->generateUrl(
                            'access_invite_person',
                            ['invitationHash' => $person->getInvitationHash()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );

                        $emailService->sendActionEmail($person->getEmail(), $subject, $body, $actionText, $actionLink);

                        $this->displaySuccess($translator->trans('invite_resend.success.email_send', [], 'access'));
                    } else {
                        $this->displayError($translator->trans('invite_resend.error.no_invitation_sent_yet', [], 'access'));
                    }
                } else {
                    $this->displayError($translator->trans('invite_resend.error.invitation_already_accepted', [], 'access'));
                }
            }

            $member = $this->getDoctrine()->getRepository('App:Member')->findOneBy(['email' => $email]);
            if (null !== $member) {
                if ($member->getHasBeenInvited()) {
                    //resend member invite email

                    $subject = $translator->trans('resend_invitation.subject', [], 'email_access');
                    $body = $translator->trans('resend_invitation.message', [], 'email_access');
                    $actionText = $translator->trans('resend_invitation.action_text', [], 'email_access');
                    $actionLink = $this->generateUrl(
                        'access_invite',
                        ['invitationHash' => $member->getInvitationHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $emailService->sendActionEmail($person->getEmail(), $subject, $body, $actionText, $actionLink);

                    $this->displaySuccess($translator->trans('invite_resend.success.email_send', [], 'access'));
                } else {
                    $this->displayError($translator->trans('invite_resend.error.no_invitation_sent_yet', [], 'access'));
                }
            }

            if (null === $member && null === $person) {
                $this->displayError($translator->trans('invite_resend.error.email_not_found', [], 'access'));
            }
        }

        return $this->renderWithBackUrl(
            'access/invite_resend.html.twig',
            [],
            $this->generateUrl('access_login')
        );
    }


    /**
     * @Route("/invite/{invitationIdentifier}", name="access_invite")
     *
     * @param Request $request
     * @param $invitationHash
     * @param TranslatorInterface $translator
     *
     * @return FormInterface|Response
     */
    public function inviteAction(Request $request, $invitationHash, TranslatorInterface $translator)
    {
        $member = $this->getDoctrine()->getRepository('App:Member')->findOneBy(['invitationIdentifier' => $invitationIdentifier]);
        if (!$member instanceof Member) {
            return $this->renderWithBackUrl(
                'access/invitation_hash_invalid.html.twig',
                [],
                $this->generateUrl('access_login')
            );
        }

        //add user if already registered
        if ($this->getUser() instanceof FrontendUser) {
            $person = $this->getPerson();
            //already logged in!
            foreach ($member->getFrontendUsers() as $memberPerson) {
                if ($memberPerson->getId() === $person->getId()) {
                    $this->displayInfo(
                        $translator->trans(
                            'invite.messages.already_part_of_member',
                            ['%member%' => $member->getName(), '%organisation%' => $member->getOrganisation()->getName()],
                            'access'
                        )
                    );
                    return $this->redirectToRoute('dashboard_index');
                }
            }
            $person->addMember($member);
            $member->addPerson($person);
            $this->fastSave($member, $person);

            $this->displayInfo(
                $translator->trans(
                    'invite.messages.now_part_of_member',
                    ['%member%' => $member->getName(), '%organisation%' => $member->getOrganisation()->getName()],
                    'access'
                )
            );

            return $this->redirectToRoute('dashboard_about');
        }
        $person = new Person();
        $person->setEmail($member->getEmail());
        $inviteForm = $this->handleForm(
            $this->createForm(MemberInviteType::class),
            $request,
            $translator,
            $person,
            function ($form, $person) use ($member, $request, $translator) {
                /* @var Person $person */
                $existingUser = $this->getDoctrine()->getRepository('App:FrontendUser')->findOneBy(['email' => $person->getEmail()]);
                if (null !== $existingUser) {
                    $this->displayError($translator->trans('error.email_already_registered', [], 'access'));

                    return $form;
                }
                $person->addMember($member);
                $member->addPerson($person);

                $user = $person->getFrontendUser();
                $user->persistNewPassword();
                $user->setIsActive(true);
                $user->setRegistrationDate(new \DateTime());
                $person->setEmail($user->getEmail());

                $this->fastSave($person, $member);

                $this->loginUser($request, $person->getFrontendUser());
                $this->displaySuccess($translator->trans('success.welcome', [], 'access'));

                return $this->redirectToRoute('dashboard_about');
            }
        );

        if ($inviteForm instanceof RedirectResponse) {
            return $inviteForm;
        }

        $arr['member'] = $member;
        $arr['organisation'] = $member->getOrganisation();
        $arr['invite_form'] = $inviteForm->createView();

        return $this->renderWithBackUrl(
            'access/invite.html.twig',
            $arr,
            $this->generateUrl('access_login')
        );
    }
}