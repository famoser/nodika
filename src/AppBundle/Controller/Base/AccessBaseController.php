<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/06/2017
 * Time: 13:36
 */

namespace AppBundle\Controller\Base;


use AppBundle\Entity\Traits\UserTrait;
use AppBundle\Form\Access\Base\LoginType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AccessBaseController extends BaseController
{
    /**
     * @param Request $request
     * @param UserTrait $user
     * @param $translationDomain
     * @return Form
     */
    protected function getLoginForm(Request $request, $user, $translationDomain)
    {
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

        $user->setEmail($lastUsername);


        $loginForm = $this->createForm(LoginType::class, $user);
        $loginForm->handleRequest($request);

        if ($loginForm->isSubmitted()) {
            throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
        }

        return $loginForm;
    }

    /**
     * @param Request $request
     * @param Form $registerForm
     * @param UserTrait $user
     * @param EntityRepository $repository
     * @param callable $beforeInsertCallback
     * @param callable $generateRegisterConfirmLink
     * @param callable $generateRegisterThanksLink
     * @return Form
     */
    protected function getRegisterForm(Request $request, $registerForm, $user, $repository, $beforeInsertCallback, $generateRegisterConfirmLink, $generateRegisterThanksLink)
    {
        $registerForm->setData($user);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted()) {
            if ($registerForm->isValid()) {
                if ($beforeInsertCallback($user)) {
                    $existingUser = $repository->findOneBy(["email" => $user->getEmail()]);
                    /* @var $existingUser UserTrait */
                    if ($existingUser != null) {
                        $this->displayError($this->get("translator")->trans("error.email_already_registered", [], "access"));
                    } else {
                        if (!$user->isValidPlainPassword()) {
                            $this->displayError($this->get("translator")->trans("error.new_password_not_valid", [], "access"));
                        } else {
                            $user->hashAndRemovePlainPassword();
                            $user->createNewResetHash();
                            $user->setRegistrationDate(new \DateTime());

                            $em = $this->getDoctrine()->getManager();
                            $em->persist($user);
                            $em->flush();

                            $translate = $this->get("translator");
                            $registerLink = $generateRegisterConfirmLink($user);


                            $message = \Swift_Message::newInstance()
                                ->setSubject($translate->trans("register.subject", [], "email_access"))
                                ->setFrom($this->getParameter("mailer_email"))
                                ->setTo($user->getEmail())
                                ->setBody($translate->trans(
                                    "register.message",
                                    ["%register_link%" => $registerLink],
                                    "email_access"));
                            $this->get('mailer')->send($message);
                            return $generateRegisterThanksLink($user);
                        }
                    }
                }
            } else {
                $this->displayFormValidationError();
            }
        }
        return $registerForm;
    }

    /**
     * @param Request $request
     * @param EntityRepository $repository
     * @param callable $generateResetLink
     * @param callable $generateResetDoneLink
     * @return \Symfony\Component\Form\FormInterface|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function getResetForm(Request $request, $repository, $generateResetLink, $generateResetDoneLink)
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

        $resetForm->handleRequest($request);

        if ($resetForm->isSubmitted()) {
            if ($resetForm->isValid()) {
                /* @var $existingUser UserTrait */
                $existingUser = $repository->findOneBy(["email" => $resetForm->get("email")->getData()]);
                if ($existingUser != null) {
                    $existingUser->createNewResetHash();

                    $this->getDoctrine()->getManager()->persist($existingUser);
                    $this->getDoctrine()->getManager()->flush();

                    $translate = $this->get("translator");
                    $resetLink = $generateResetLink($existingUser);

                    $message = \Swift_Message::newInstance()
                        ->setSubject($translate->trans("reset.subject", [], "email_access"))
                        ->setFrom($this->getParameter("mailer_email"))
                        ->setTo($existingUser->getEmail())
                        ->setBody($translate->trans(
                            "reset.message",
                            ["%reset_link%" => $resetLink],
                            "email_access"));
                    $this->get('mailer')->send($message);
                }
                return $generateResetDoneLink($existingUser);
            } else {
                $this->displayFormValidationError();
            }
        }
        return $resetForm;
    }

    /**
     * @param Request $request
     * @param EntityRepository $repository
     * @param string $confirmationToken
     * @param Form $setPasswordForm
     * @return bool|Form|Response
     */
    protected function processConfirmationToken(Request $request, $repository, $confirmationToken, $setPasswordForm)
    {
        /* @var $user UserTrait */
        $user = $repository->findOneBy(["resetHash" => $confirmationToken]);
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
}