<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:19
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseAccessController;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Member;
use AppBundle\Entity\Person;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\FrontendUser\FrontendUserLoginType;
use AppBundle\Form\FrontendUser\FrontendUserResetType;
use AppBundle\Form\FrontendUser\FrontendUserSetPasswordType;
use AppBundle\Form\Person\PersonType;
use AppBundle\Helper\HashHelper;
use AppBundle\Helper\StaticMessageHelper;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

        return $this->render(
            'access/login.html.twig', $arr
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
                $existingUser = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["email" => $person->getEmail()]);
                if ($existingUser != null) {
                    $this->displayError($this->get("translator")->trans("error.email_already_registered", [], "access"));
                    return $form;
                } else {
                    $user = FrontendUser::createFromPerson($person);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($person);
                    $em->persist($user);
                    $em->flush();

                    $translate = $this->get("translator");
                    $registerLink = $this->generateUrl(
                        "access_register_confirm",
                        ["confirmationToken" => $user->getResetHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $message = \Swift_Message::newInstance()
                        ->setSubject($translate->trans("register.subject", [], "email_access"))
                        ->setFrom($this->getParameter("mailer_email"))
                        ->setTo($user->getEmail())
                        ->setBody($translate->trans(
                            "register.message",
                            ["%register_link%" => $registerLink],
                            "email_access"));
                    $this->get('mailer')->send($message);
                    return $this->redirectToRoute("access_register_thanks");
                }
            }
        );

        if ($registerForm instanceof RedirectResponse) {
            return $registerForm;
        }

        $arr["register_form"] = $registerForm->createView();
        return $this->render(
            'access/register.html.twig', $arr
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
        $member = $this->getDoctrine()->getRepository("AppBundle:Member")->findOneBy(["invitationHash" => $invitationHash]);
        if (!$member instanceof Member) {
            return $this->render(
                'access/invitation_hash_invalid.html.twig', []
            );
        }
        if ($this->getUser() instanceof FrontendUser) {
            //already logged in!
            foreach ($member->getPersons() as $person) {
                //if ($person->getId())
            }
            if ($member->getPersons()) {}
        }
        $registerForm = $this->handleFormDoctrinePersist(
            $this->createCrudForm(PersonType::class, SubmitButtonType::REGISTER),
            $request,
            new Person(),
            function ($form, $person) {
                /* @var Person $person */
                $existingUser = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["email" => $person->getEmail()]);
                if ($existingUser != null) {
                    $this->displayError($this->get("translator")->trans("error.email_already_registered", [], "access"));
                    return $form;
                } else {
                    $user = FrontendUser::createFromPerson($person);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($person);
                    $em->persist($user);
                    $em->flush();

                    $translate = $this->get("translator");
                    $registerLink = $this->generateUrl(
                        "access_register_confirm",
                        ["confirmationToken" => $user->getResetHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $message = \Swift_Message::newInstance()
                        ->setSubject($translate->trans("register.subject", [], "email_access"))
                        ->setFrom($this->getParameter("mailer_email"))
                        ->setTo($user->getEmail())
                        ->setBody($translate->trans(
                            "register.message",
                            ["%register_link%" => $registerLink],
                            "email_access"));
                    $this->get('mailer')->send($message);
                    return $this->redirectToRoute("access_register_thanks");
                }
            }
        );

        if ($registerForm instanceof RedirectResponse) {
            return $registerForm;
        }

        $arr["register_form"] = $registerForm->createView();
        return $this->render(
            'access/register.html.twig', $arr
        );
    }

    /**
     * @Route("/register/thanks", name="access_register_thanks")
     * @param Request $request
     * @return Response
     */
    public function registerThanksAction(Request $request)
    {
        return $this->render(
            'access/register_thanks.html.twig'
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

                $existingUser = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["email" => $entity->getEmail()]);
                if ($existingUser != null) {
                    $existingUser->setResetHash(HashHelper::createNewResetHash());

                    $this->getDoctrine()->getManager()->persist($existingUser);
                    $this->getDoctrine()->getManager()->flush();

                    $translate = $this->get("translator");
                    $resetLink = $this->generateUrl(
                        "access_reset_confirm",
                        ["confirmationToken" => $existingUser->getResetHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $message = \Swift_Message::newInstance()
                        ->setSubject($translate->trans("reset.subject", [], "email_access"))
                        ->setFrom($this->getParameter("mailer_email"))
                        ->setTo($existingUser->getEmail())
                        ->setBody($translate->trans(
                            "reset.message",
                            ["%reset_link%" => $resetLink],
                            "email_access"));
                    $this->get('mailer')->send($message);
                } else {
                    $this->get("logger")->error("tried to reset password for non-existing user " . $existingUser->getEmail());
                }
                return $this->redirectToRoute("access_reset_done");
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr = [];
        $arr["reset_form"] = $myForm->createView();
        return $this->render(
            'access/reset.html.twig', $arr
        );
    }

    /**
     * @Route("/reset/done", name="access_reset_done")
     * @param Request $request
     * @return Response
     */
    public function resetDoneAction(Request $request)
    {
        return $this->render(
            'access/reset_done.html.twig'
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
                return $this->redirectToRoute("administration_organisation_new");
            },
            function ($form) {
                /* @var FormInterface $form */
                $outputArray["set_password_form"] = $form->createView();
                return $this->render(
                    'access/register_confirm.html.twig', $outputArray
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
                return $this->render(
                    'access/reset_confirm.html.twig', $outputArray
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
        $user = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["resetHash" => $confirmationToken]);
        if ($user == null) {
            return $this->render(
                'access/hash_invalid.html.twig'
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