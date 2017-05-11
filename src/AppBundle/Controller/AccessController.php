<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:19
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Person;
use AppBundle\Form\Access\LoginType;
use AppBundle\Form\Access\RegisterType;
use AppBundle\Form\Access\SetPasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AccessController extends BaseController
{
    /**
     * @Route("/login", name="access_login")
     */
    public function loginAction(Request $request)
    {
        $arr = [];

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }
        if ($error != null) {
            $this->displayError($this->get("translator")->trans("error.login_failed", [], "access"));
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

        $user = new FrontendUser();
        $user->setEmail($lastUsername);


        $loginForm = $this->get("form.factory")->createNamedBuilder(
            null,
            FormType::class,
            ["_username" => $user->getEmail()],
            ["translation_domain" => "access"]
        )
            ->add("_username", EmailType::class)
            ->add("_password", PasswordType::class)
            ->add("login", SubmitType::class)
            ->getForm();


        $loginForm->handleRequest($request);

        if ($loginForm->isSubmitted()) {
            throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
        }

        $arr["login_form"] = $loginForm->createView();

        return $this->render(
            'access/login.html.twig', $arr
        );
    }

    /**
     * @Route("/register", name="access_register")
     */
    public function registerAction(Request $request)
    {
        $registerForm = $this->createForm(RegisterType::class);
        $arr = [];

        $person = new Person();
        $registerForm->setData($person);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted()) {
            if ($registerForm->isValid()) {
                $existingUser = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["email" => $person->getEmail()]);
                if ($existingUser != null) {
                    $this->displayError($this->get("translator")->trans("error.email_already_registered", [], "access"));
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
                        ->setSubject($translate->trans("register.subject", [], "access_emails"))
                        ->setFrom($this->getParameter("mailer_email"))
                        ->setTo($user->getEmail())
                        ->setBody($translate->trans(
                            "register.message",
                            ["%register_link%" => $registerLink],
                            "access_emails"));
                    $this->get('mailer')->send($message);
                    return $this->redirectToRoute("access_register_thanks");
                }
            } else {
                $this->displayFormValidationError();
            }
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
     */
    public function resetAction(Request $request)
    {
        $resetForm = $this->get("form.factory")->createNamedBuilder(
            null,
            FormType::class,
            [],
            ["translation_domain" => "access"]
        )
            ->add("email", EmailType::class)
            ->add("reset", SubmitType::class)
            ->getForm();

        $arr = [];

        $resetForm->handleRequest($request);

        if ($resetForm->isSubmitted()) {
            if ($resetForm->isValid()) {
                $existingUser = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["email" => $resetForm->get("email")->getData()]);
                if ($existingUser != null) {
                    $existingUser->createNewResetHash();

                    $this->getDoctrine()->getManager()->persist($existingUser);
                    $this->getDoctrine()->getManager()->flush();

                    $translate = $this->get("translator");
                    $resetLink = $this->generateUrl(
                        "access_reset_confirm",
                        ["confirmationToken" => $existingUser->getResetHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $message = \Swift_Message::newInstance()
                        ->setSubject($translate->trans("reset.subject", [], "access_emails"))
                        ->setFrom($this->getParameter("mailer_email"))
                        ->setTo($existingUser->getEmail())
                        ->setBody($translate->trans(
                            "reset.message",
                            ["%reset_link%" => $resetLink],
                            "access_emails"));
                    $this->get('mailer')->send($message);
                    return $this->redirectToRoute("access_reset_done");
                }
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["reset_form"] = $resetForm->createView();
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
     * @Route("/reset/{confirmationToken}", name="access_reset_confirm")
     * @param Request $request
     * @param $confirmationToken
     * @return Response
     */
    public function resetConfirmAction(Request $request, $confirmationToken)
    {
        $result = $this->processConfirmationToken($request, $confirmationToken);
        if ($result instanceof Response) {
            return $result;
        } else if ($result === true) {
            return $this->redirectToRoute("dashboard_start");
        } else {
            $arr["reset_password_form"] = $result->createView();
            return $this->render(
                'access/reset_confirm.html.twig', $arr
            );
        }
    }

    /**
     * @Route("/register/{confirmationToken}", name="access_register_confirm")
     * @param Request $request
     * @param $confirmationToken
     * @return Response
     */
    public function registerConfirmAction(Request $request, $confirmationToken)
    {
        $result = $this->processConfirmationToken($request, $confirmationToken);
        if ($result instanceof Response) {
            return $result;
        } else if ($result === true) {
            return $this->redirectToRoute("administration_organisation_new");
        } else {
            $arr["register_confirm_form"] = $result->createView();
            return $this->render(
                'access/register_confirm.html.twig', $arr
            );
        }
    }

    /**
     * @param Request $request
     * @param $confirmationToken
     * @return bool|Form|Response
     */
    private function processConfirmationToken(Request $request, $confirmationToken)
    {
        $setPasswordForm = $this->createForm(SetPasswordType::class);

        $user = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["resetHash" => $confirmationToken]);
        if ($user == null) {
            return $this->render(
                'access/hash_invalid.html.twig'
            );
        }
        $setPasswordForm->setData($user);
        $setPasswordForm->handleRequest($request);

        if ($setPasswordForm->isSubmitted()) {
            if ($setPasswordForm->isValid()) {
                if ($user->isValidPlainPassword()) {
                    if ($user->getPlainPassword() == $user->getRepeatPlainPassword()) {
                        $user->hashAndRemovePlainPassword();
                        $user->createNewResetHash();

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();

                        //login programmatically
                        $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
                        $this->get("security.token_storage")->setToken($token);

                        $event = new InteractiveLoginEvent($request, $token);
                        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                        return true;
                    } else {
                        $this->displayError($this->get("translator")->trans("error.passwords_do_not_match", [], "access"));
                    }
                } else {
                    $this->displayError($this->get("translator")->trans("error.new_password_not_valid", [], "access"));
                }
            } else {
                $this->displayFormValidationError();
            }
        }
        return $setPasswordForm;
    }

    /**
     * @Route("/login_check", name="access_login_check")
     */
    public function loginCheck(Request $request)
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }
}