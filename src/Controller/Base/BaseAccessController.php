<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Base;

use App\Entity\Traits\UserTrait;
use App\Helper\HashHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;

class BaseAccessController extends BaseFrontendController
{
    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param UserTrait $user
     * @param FormInterface $loginForm
     *
     * @return FormInterface
     */
    protected function getLoginForm(Request $request, TranslatorInterface $translator, $user, FormInterface $loginForm)
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
        if (null !== $error) {
            $this->displayError($translator->trans('error.login_failed', [], 'access'));
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);
        $user->setEmail($lastUsername);

        $loginForm->handleRequest($request);

        if ($loginForm->isSubmitted()) {
            throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
        }

        return $loginForm;
    }

    /**
     * @param AdvancedUserInterface $user
     * @param Request               $request
     */
    protected function loginUser(Request $request, AdvancedUserInterface $user)
    {
        //login programmatically
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
    }
}
