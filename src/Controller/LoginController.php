<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseFormController;
use App\Entity\Doctor;
use App\Form\Traits\User\ChangePasswordType;
use App\Form\Traits\User\LoginType;
use App\Form\Traits\User\RecoverType;
use App\Form\Traits\User\RequestInviteType;
use App\Model\Breadcrumb;
use App\Service\Interfaces\EmailServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/login")
 */
class LoginController extends BaseFormController
{
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() +
            [
                'event_dispatcher' => EventDispatcherInterface::class,
                'security.token_storage' => TokenStorageInterface::class,
                'translator' => TranslatorInterface::class,
            ];
    }

    /**
     * @param Request       $request
     * @param UserInterface $user
     */
    protected function loginUser(Request $request, UserInterface $user)
    {
        //login programmatically
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
    }

    /**
     * @Route("", name="login")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $user = new Doctor();
        $form = $this->createForm(LoginType::class, $user);
        $form->add('form.login', SubmitType::class, ['translation_domain' => 'login', 'label' => 'login.do_login']);

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
        if (null !== $error) {
            $this->displayError($this->getTranslator()->trans('login.danger.login_failed', [], 'login'));
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);
        $user->setEmail($lastUsername);

        $form->handleRequest($request);

        $arr['form'] = $form->createView();

        return $this->render('login/login.html.twig', $arr);
    }

    /**
     * @Route("/recover", name="login_recover")
     *
     * @param Request               $request
     * @param EmailServiceInterface $emailService
     * @param TranslatorInterface   $translator
     *
     * @return Response
     */
    public function recoverAction(Request $request, EmailServiceInterface $emailService, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $form = $this->handleForm(
            $this->createForm(RecoverType::class)
                ->add('form.recover', SubmitType::class, ['translation_domain' => 'login', 'label' => 'recover.title']),
            $request,
            function ($form) use ($emailService, $translator, $logger) {
                /* @var FormInterface $form */

                //display success
                $this->displaySuccess($translator->trans('recover.success.email_sent', [], 'login'));

                //check if user exists
                $exitingUser = $this->getDoctrine()->getRepository(Doctor::class)->findOneBy(['email' => $form->getData()['email']]);
                if (null === $exitingUser) {
                    $logger->warning('tried to reset passwort for non-exitant email '.$form->getData()['email']);

                    return $form;
                }

                //create new reset hash
                $exitingUser->setResetHash();
                $this->fastSave($exitingUser);

                //sent according email
                $emailService->sendActionEmail(
                    $exitingUser->getEmail(),
                    $translator->trans('recover.email.reset_password.subject', [], 'login'),
                    $translator->trans('recover.email.reset_password.message', [], 'login'),
                    $translator->trans('recover.email.reset_password.action_text', [], 'login'),
                    $this->generateUrl('login_reset', ['resetHash' => $exitingUser->getResetHash()], UrlGeneratorInterface::ABSOLUTE_URL)
                );
                $logger->warning('reset email sent to '.$exitingUser->getEmail());

                return $form;
            }
        );
        $arr['form'] = $form->createView();

        return $this->render('login/recover.html.twig', $arr);
    }

    /**
     * @Route("/reset/{resetHash}", name="login_reset")
     *
     * @param Request $request
     * @param $resetHash
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function resetAction(Request $request, $resetHash, TranslatorInterface $translator)
    {
        $user = $this->getDoctrine()->getRepository(Doctor::class)->findOneBy(['resetHash' => $resetHash]);
        if (null === $user) {
            $this->displayError($translator->trans('reset.danger.invalid_hash', [], 'login'));

            return new RedirectResponse($this->generateUrl('login'));
        }

        $form = $this->handleForm(
            $this->createForm(ChangePasswordType::class, $user, ['data_class' => Doctor::class])
                ->add('form.set_password', SubmitType::class, ['translation_domain' => 'login', 'label' => 'reset.set_password']),
            $request,
            function ($form) use ($user, $translator, $request) {
                //check for valid password
                if ($user->getPlainPassword() !== $user->getRepeatPlainPassword()) {
                    $this->displayError($translator->trans('reset.danger.passwords_do_not_match', [], 'login'));

                    return $form;
                }

                //display success
                $this->displaySuccess($translator->trans('reset.success.password_set', [], 'login'));

                //set new password & save
                $user->setPassword();
                $user->setResetHash();
                $this->fastSave($user);

                //login user & redirect
                $this->loginUser($request, $user);

                return $this->redirectToRoute('index_index');
            }
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr['form'] = $form->createView();

        return $this->render('login/reset.html.twig', $arr);
    }

    /**
     * @Route("/request_invite", name="login_request_invite")
     *
     * @param Request               $request
     * @param EmailServiceInterface $emailService
     * @param TranslatorInterface   $translator
     *
     * @return Response
     */
    public function requestAction(Request $request, EmailServiceInterface $emailService, TranslatorInterface $translator)
    {
        $form = $this->handleForm(
            $this->createForm(RequestInviteType::class)
                ->add('form.request_invite', SubmitType::class),
            $request,
            function ($form) use ($emailService, $translator) {
                /* @var FormInterface $form */

                //display success
                $this->displaySuccess($translator->trans('request_invite.success.email_sent', [], 'login'));

                //check if user exists
                $exitingUser = $this->getDoctrine()->getRepository(Doctor::class)->findOneBy(['email' => $form->getData()['email']]);
                if (null === $exitingUser) {
                    return $form;
                }

                //sent invite email
                $emailService->sendActionEmail(
                    $exitingUser->getEmail(),
                    $translator->trans('invite.email.subject', [], 'invite'),
                    $translator->trans('invite.email.message', [], 'invite'),
                    $translator->trans('invite.email.action_text', [], 'invite'),
                    $this->generateUrl('invite_index', ['guid' => $exitingUser->getInvitationIdentifier()], UrlGeneratorInterface::ABSOLUTE_URL)
                );

                return $form;
            }
        );
        $arr['form'] = $form->createView();

        return $this->render('login/request_invite.html.twig', $arr);
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl('login'),
                $this->getTranslator()->trans('login.title', [], 'login')
            ),
        ];
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="login_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}
