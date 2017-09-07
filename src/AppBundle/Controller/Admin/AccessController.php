<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:19
 */

namespace AppBundle\Controller\Admin;


use AppBundle\Controller\Base\AccessBaseController;
use AppBundle\Entity\AdminUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends AccessBaseController
{
    /**
     * @Route("/login", name="admin_login")
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $user = $this->getUser();
        if ($user instanceof AdminUser) {
            return $this->redirectToRoute("admin_dashboard");
        }

        $form = $this->getLoginForm($request, new AdminUser(), "admin_login");
        if ($form instanceof RedirectResponse) {
            return $form;
        }
        $arr["login_form"] = $form->createView();

        return $this->render(
            'admin/access/login.html.twig', $arr
        );
    }

    /**
     * @Route("/", name="admin_default")
     * @param Request $request
     * @return Response
     */
    public function defaultAction(Request $request)
    {
        return $this->redirectToRoute("admin_login");
    }

    /**
     * @Route("/login_check", name="admin_login_check")
     * @param Request $request
     */
    public function loginCheck(Request $request)
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="admin_logout")
     * @param Request $request
     */
    public function logoutAction(Request $request)
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}