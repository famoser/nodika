<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Backend;

use App\Controller\Base\BaseAccessController;
use App\Entity\AdminUser;
use App\Form\AdminUser\AdminUserLoginType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class AccessController extends BaseAccessController
{
    /**
     * @Route("/login", name="backend_login")
     *
     * @param Request             $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function loginAction(Request $request, TranslatorInterface $translator)
    {
        $user = $this->getUser();
        if ($user instanceof AdminUser) {
            return $this->redirectToRoute('backend_dashboard_index');
        }

        $form = $this->getLoginForm($request, $translator, new AdminUser(), $this->createForm(AdminUserLoginType::class));
        if ($form instanceof RedirectResponse) {
            return $form;
        }
        $arr['login_form'] = $form->createView();

        return $this->renderWithBackUrl(
            'backend/access/login.html.twig',
            $arr,
            $this->generateUrl('homepage')
        );
    }

    /**
     * @Route("/", name="backend_default")
     *
     * @return Response
     */
    public function defaultAction()
    {
        return $this->redirectToRoute('backend_login');
    }

    /**
     * @Route("/login_check", name="backend_login_check")
     */
    public function loginCheck()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="backend_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}
