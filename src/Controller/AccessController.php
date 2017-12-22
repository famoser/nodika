<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:19
 */

namespace App\Controller;


use App\Controller\Base\BaseAccessController;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Entity\Person;
use App\Enum\SubmitButtonType;
use App\Form\FrontendUser\FrontendUserLoginType;
use App\Form\FrontendUser\FrontendUserResetType;
use App\Form\FrontendUser\FrontendUserSetPasswordType;
use App\Form\Member\MemberInviteType;
use App\Form\Person\PersonInviteType;
use App\Form\Person\PersonType;
use App\Helper\HashHelper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccessController extends BaseAccessController
{
    /**
     * @Route("/login", name="access_login")
     * @param Request $request
     * @return \Symfony\Component\Form\Form|RedirectResponse|Response
     */
    public function loginAction(Request $request)
    {
        $user = $this->getUser();
        if ($user instanceof FrontendUser) {
            return $this->redirectToRoute("dashboard_index");
        }

        $form = $this->getLoginForm($request, new FrontendUser(), $this->createForm(FrontendUserLoginType::class));
        if ($form instanceof RedirectResponse) {
            return $form;
        }
        $arr["login_form"] = $form->createView();

        return $this->renderWithBackUrl(
            'access/login.html.twig', $arr, $this->generateUrl("homepage")
        );
    }

    /**
     * @Route("/register/check", name="access_register_check")
     * @param Request $request
     * @return FormInterface|Response
     */
    public function registerCheckAction(Request $request)
    {
        return $this->renderWithBackUrl(
            'access/register_check.html.twig', [], $this->generateUrl("access_login")
        );
    }

    /**
     * @Route("/register", name="access_register")
     * @param Request $request
     * @return FormInterface|Response
     */
    public function registerAction(Request $request)
    {
        $registerForm = $this->handleFormDoctrinePersist(
            $this->createCrudForm(PersonType::class, SubmitButtonType::REGISTER),
            $request,
            new Person(),
            function ($form, $person) {
                /* @var Person $person */
                $existingUser = $this->getDoctrine()->getRepository("App:FrontendUser")->findOneBy(["email" => $person->getEmail()]);
                if ($existingUser != null) {
                    $this->displayError($this->get("translator")->trans("error.email_already_registered", [], "access"));
                    return $form;
                } else {
                    $user = FrontendUser::createFromPerson($person);
                    $this->fastSave($person, $user);


                    $translator = $this->get("translator");
                    $subject = $translator->trans("register.subject", [], "email_access");
                    $receiver = $existingUser->getEmail();
                    $body = $translator->trans("register.message", [], "email_access");
                    $actionText = $translator->trans("register.action_text", [], "email_access");
                    $actionLink = $this->generateUrl(
                        "access_register_confirm",
                        ["confirmationToken" => $user->getResetHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $this->get("app.email_service")->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink);
                    return $this->redirectToRoute("access_register_thanks");
                }
            }
        );

        if ($registerForm instanceof RedirectResponse) {
            return $registerForm;
        }

        $arr["register_form"] = $registerForm->createView();
        return $this->renderWithBackUrl(
            'access/register.html.twig', $arr, $this->generateUrl("access_login")
        );
    }


    /**
     * @Route("/invite/resend", name="access_invite_resend")
     * @param Request $request
     * @return FormInterface|Response
     */
    public function inviteResendAction(Request $request)
    {
        if ($request->getMethod() == "POST") {
            $email = $request->get("email");
            $translator = $this->get("translator");

            $person = $this->getDoctrine()->getRepository("App:Person")->findOneBy(["email" => $email]);
            if ($person != null) {
                if ($person->getFrontendUser() == null) {
                    if ($person->getHasBeenInvited()) {
                        //resend invite email
                        $subject = $translator->trans("resend_invitation.subject", [], "email_access");
                        $body = $translator->trans("resend_invitation.message", [], "email_access");
                        $actionText = $translator->trans("resend_invitation.action_text", [], "email_access");
                        $actionLink = $this->generateUrl(
                            "access_invite_person",
                            ["invitationHash" => $person->getInvitationHash()],
                            UrlGeneratorInterface::ABSOLUTE_URL);

                        $this->get("app.email_service")->sendActionEmail($person->getEmail(), $subject, $body, $actionText, $actionLink);

                        $this->displaySuccess($translator->trans("invite_resend.success.email_send", [], "access"));
                    } else {
                        $this->displayError($translator->trans("invite_resend.error.no_invitation_sent_yet", [], "access"));
                    }
                } else {
                    $this->displayError($translator->trans("invite_resend.error.invitation_already_accepted", [], "access"));
                }
            }

            $member = $this->getDoctrine()->getRepository("App:Member")->findOneBy(["email" => $email]);
            if ($member != null) {
                if ($member->getHasBeenInvited()) {
                    //resend member invite email

                    $subject = $translator->trans("resend_invitation.subject", [], "email_access");
                    $body = $translator->trans("resend_invitation.message", [], "email_access");
                    $actionText = $translator->trans("resend_invitation.action_text", [], "email_access");
                    $actionLink = $this->generateUrl(
                        "access_invite",
                        ["invitationHash" => $member->getInvitationHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL);

                    $this->get("app.email_service")->sendActionEmail($person->getEmail(), $subject, $body, $actionText, $actionLink);

                    $this->displaySuccess($translator->trans("invite_resend.success.email_send", [], "access"));
                } else {
                    $this->displayError($translator->trans("invite_resend.error.no_invitation_sent_yet", [], "access"));
                }
            }

            if ($member == null && $person == null) {
                $this->displayError($translator->trans("invite_resend.error.email_not_found", [], "access"));
            }
        }


        return $this->renderWithBackUrl(
            'access/invite_resend.html.twig', [], $this->generateUrl("access_login")
        );
    }

    /**
     * @Route("/invite/{invitationHash}", name="access_invite")
     * @param Request $request
     * @param $invitationHash
     * @return FormInterface|Response
     */
    public function inviteAction(Request $request, $invitationHash)
    {
        $member = $this->getDoctrine()->getRepository("App:Member")->findOneBy(["invitationHash" => $invitationHash]);
        if (!$member instanceof Member) {
            return $this->renderWithBackUrl(
                'access/invitation_hash_invalid.html.twig', [], $this->generateUrl("access_login")
            );
        }

        //add user if already registered
        if ($this->getUser() instanceof FrontendUser) {
            $translator = $this->get("translator");
            $found = false;

            $person = $this->getPerson();
            //already logged in!
            foreach ($member->getPersons() as $memberPerson) {
                if ($memberPerson->getId() == $person->getId()) {
                    $this->displayInfo(
                        $translator->trans(
                            "invite.messages.already_part_of_member",
                            ["%member%" => $member->getName(), "%organisation%" => $member->getOrganisation()->getName()],
                            "access"
                        )
                    );
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $person->addMember($member);
                $member->addPerson($person);
                $this->fastSave($member, $person);

                $this->displayInfo(
                    $translator->trans(
                        "invite.messages.now_part_of_member",
                        ["%member%" => $member->getName(), "%organisation%" => $member->getOrganisation()->getName()],
                        "access"
                    )
                );
            }
            return $this->redirectToRoute("dashboard_index");
        }
        $person = new Person();
        $person->setEmail($member->getEmail());
        $inviteForm = $this->handleForm(
            $this->createForm(MemberInviteType::class),
            $request,
            $person,
            function ($form, $person) use ($member, $request) {
                /* @var Person $person */
                $existingUser = $this->getDoctrine()->getRepository("App:FrontendUser")->findOneBy(["email" => $person->getEmail()]);
                if ($existingUser != null) {
                    $this->displayError($this->get("translator")->trans("error.email_already_registered", [], "access"));
                    return $form;
                } else {
                    $person->addMember($member);
                    $member->addPerson($person);

                    $user = $person->getFrontendUser();
                    $user->persistNewPassword();
                    $user->setIsActive(true);
                    $user->setRegistrationDate(new \DateTime());
                    $person->setEmail($user->getEmail());

                    $this->fastSave($person, $member);

                    $this->loginUser($request, $person->getFrontendUser());
                    $this->displaySuccess($this->get("translator")->trans("success.welcome", [], "access"));
                    return $this->redirectToRoute("dashboard_index");
                }
            }
        );

        if ($inviteForm instanceof RedirectResponse) {
            return $inviteForm;
        }

        $arr["member"] = $member;
        $arr["organisation"] = $member->getOrganisation();
        $arr["invite_form"] = $inviteForm->createView();
        return $this->renderWithBackUrl(
            'access/invite.html.twig', $arr, $this->generateUrl("access_login")
        );
    }


    /**
     * @Route("/invite/person/{invitationHash}", name="access_invite_person")
     * @param Request $request
     * @param $invitationHash
     * @return FormInterface|Response
     */
    public function invitePersonAction(Request $request, $invitationHash)
    {
        $person = $this->getDoctrine()->getRepository("App:Person")->findOneBy(["invitationHash" => $invitationHash]);
        if (!$person instanceof Person) {
            return $this->renderWithBackUrl(
                'access/invitation_hash_invalid.html.twig', [], $this->generateUrl("access_login")
            );
        }

        if ($this->getUser() instanceof FrontendUser) {
            if ($this->getUser()->getEmail() == $person->getEmail()) {
                if ($this->getUser()->getPerson()->getId() != $person->getId()) {
                    $this->getUser()->setPerson($person);
                    $person->setFrontendUser($this->getUser());
                    $this->fastSave($this->getUser(), $person);
                    $this->displaySuccess($this->get("translator")->trans("success.person_assigned", [], "access"));
                } else {
                    $this->displayInfo($this->get("translator")->trans("info.already_accepted_invite", [], "access"));
                }
                return $this->redirectToRoute("dashboard_index");
            } else {
                $this->displayError($this->get("translator")->trans("error.already_logged_in", [], "access"));
                return $this->redirectToRoute("dashboard_index");
            }
        }

        $existingUser = $this->getDoctrine()->getRepository("App:FrontendUser")->findOneBy(["email" => $person->getEmail()]);
        if ($existingUser != null) {
            $this->displayError($this->get("translator")->trans("error.email_already_registered", [], "access"));
            $this->displayInfo($this->get("translator")->trans("info.login_with_email", [], "access"));
            return $this->redirectToRoute("access_login");
        }

        $user = FrontendUser::createFromPerson($person);
        $person->setFrontendUser($user);

        $inviteForm = $this->handleForm(
            $this->createForm(PersonInviteType::class),
            $request,
            $person,
            function ($form, $person) use ($request) {
                /* @var Person $person */
                $existingUser = $this->getDoctrine()->getRepository("App:FrontendUser")->findOneBy(["email" => $person->getEmail()]);
                if ($existingUser != null) {
                    $this->displayError($this->get("translator")->trans("error.email_already_registered", [], "access"));
                    return $form;
                } else {
                    $user = $person->getFrontendUser();
                    $user->persistNewPassword();
                    $user->setIsActive(true);
                    $user->setRegistrationDate(new \DateTime());
                    $person->setEmail($user->getEmail());

                    $this->fastSave($person, $person);

                    $this->loginUser($request, $person->getFrontendUser());
                    $this->displaySuccess($this->get("translator")->trans("success.welcome", [], "access"));
                    return $this->redirectToRoute("dashboard_index");
                }
            }
        );

        if ($inviteForm instanceof RedirectResponse) {
            return $inviteForm;
        }

        $arr["person"] = $person;
        if ($person->getMembers()->count() > 0) {
            $arr["member"] = $person->getMembers()->first();
            $arr["organisation"] = $person->getMembers()->first()->getOrganisation();
        }
        $arr["invite_form"] = $inviteForm->createView();
        return $this->renderWithBackUrl(
            'access/invite.html.twig', $arr, $this->generateUrl("access_login")
        );
    }

    /**
     * @Route("/register/thanks", name="access_register_thanks")
     * @param Request $request
     * @return Response
     */
    public function registerThanksAction(Request $request)
    {
        return $this->renderNoBackUrl(
            'access/register_thanks.html.twig', [], "user needs to check email and continue there"
        );
    }

    /**
     * @Route("/reset", name="access_reset")
     * @param Request $request
     * @return Response
     */
    public function resetAction(Request $request)
    {
        $myForm = $this->handleForm(
            $this->createForm(FrontendUserResetType::class),
            $request,
            new FrontendUser(),
            function ($form, $entity) {
                /* @var FormInterface $form */
                /* @var FrontendUser $entity */

                $existingUser = $this->getDoctrine()->getRepository("App:FrontendUser")->findOneBy(["email" => $entity->getEmail()]);
                if ($existingUser != null) {
                    $existingUser->setResetHash(HashHelper::createNewResetHash());
                    $this->fastSave($existingUser);

                    $translator = $this->get("translator");
                    $subject = $translator->trans("reset.subject", [], "email_access");
                    $receiver = $existingUser->getEmail();
                    $body = $translator->trans("reset.message", [], "email_access");
                    $actionText = $translator->trans("reset.action_text", [], "email_access");
                    $actionLink = $this->generateUrl(
                        "access_reset_confirm",
                        ["confirmationToken" => $existingUser->getResetHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $this->get("app.email_service")->sendActionEmail($receiver, $subject, $body, $actionText, $actionLink);
                } else {
                    $this->get("logger")->error("tried to reset password for non-existing user " . $entity->getEmail());
                }
                return $this->redirectToRoute("access_reset_done");
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr = [];
        $arr["reset_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'access/reset.html.twig', $arr, $this->generateUrl("access_login")
        );
    }

    /**
     * @Route("/reset/done", name="access_reset_done")
     * @param Request $request
     * @return Response
     */
    public function resetDoneAction(Request $request)
    {
        return $this->renderNoBackUrl(
            'access/reset_done.html.twig', [], "user needs to check email"
        );
    }

    /**
     * @Route("/register/confirm/{confirmationToken}", name="access_register_confirm")
     * @param Request $request
     * @param $confirmationToken
     * @return Response
     */
    public function registerConfirmAction(Request $request, $confirmationToken)
    {
        return $this->handleResetPasswordAction(
            $request,
            $confirmationToken,
            function ($form, $entity) {
                /* @var FrontendUser $entity */
                if ($entity->getPerson()->getMembers()->count() == 0) {
                    return $this->redirectToRoute("administration_organisation_new");
                } else {
                    return $this->redirectToRoute("dashboard_index");
                }
            },
            function ($form) {
                /* @var FormInterface $form */
                $outputArray["set_password_form"] = $form->createView();
                return $this->renderNoBackUrl(
                    'access/register_confirm.html.twig', $outputArray, "reset was successful, user should press on dashboard"
                );
            }
        );
    }

    /**
     * @Route("/reset/confirm/{confirmationToken}", name="access_reset_confirm")
     * @param Request $request
     * @param $confirmationToken
     * @return Response
     */
    public function resetConfirmAction(Request $request, $confirmationToken)
    {
        return $this->handleResetPasswordAction(
            $request,
            $confirmationToken,
            function ($form, $entity) {
                return $this->redirectToRoute("dashboard_index");
            },
            function ($form) {
                /* @var FormInterface $form */
                $outputArray["reset_password_form"] = $form->createView();
                return $this->renderNoBackUrl(
                    'access/reset_confirm.html.twig', $outputArray, "user can now reset password, it does not make sense to go back"
                );
            }
        );
    }

    /**
     * @param Request $request
     * @param $confirmationToken
     * @param callable $onSuccessCallable with $form & $entity as argument
     * @param callable $responseCallable with $form as argument
     * @return FormInterface|Response
     */
    protected function handleResetPasswordAction(Request $request, $confirmationToken, $onSuccessCallable, $responseCallable)
    {
        $user = $this->getDoctrine()->getRepository("App:FrontendUser")->findOneBy(["resetHash" => $confirmationToken]);
        if ($user == null) {
            return $this->renderNoBackUrl(
                'access/hash_invalid.html.twig', [], $this->generateUrl("access_login")
            );
        }

        $myForm = $this->handleForm(
            $this->createForm(FrontendUserSetPasswordType::class),
            $request,
            $user,
            function ($form, $user) use ($request, $onSuccessCallable) {
                /* @var FrontendUser $user */
                if ($user->isValidPlainPassword()) {
                    if ($user->getPlainPassword() == $user->getRepeatPlainPassword()) {
                        $user->persistNewPassword();
                        $this->fastSave($user);
                        $this->loginUser($request, $user);

                        return $onSuccessCallable($form, $user);
                    } else {
                        $this->displayError($this->get("translator")->trans("error.passwords_do_not_match", [], "access"));
                    }
                } else {
                    $this->displayError($this->get("translator")->trans("error.new_password_not_valid", [], "access"));
                }
                return $form;
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        } else {
            return $responseCallable($myForm);
        }
    }

    /**
     * @Route("/login_check", name="access_login_check")
     * @param Request $request
     */
    public function loginCheck(Request $request)
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="access_logout")
     * @param Request $request
     */
    public function logoutAction(Request $request)
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}