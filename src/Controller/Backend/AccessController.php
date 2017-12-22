<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:19
 */

namespace App\Controller\Backend;


use App\Controller\Base\BaseAccessController;
use App\Entity\AdminUser;
use App\Form\AdminUser\AdminUserLoginType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends BaseAccessController
{
    /**
     * @Route("/login", name="backend_login")
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $user = $this->getUser();
        if ($user instanceof AdminUser) {
            return $this->redirectToRoute("backend_dashboard_index");
        }

        $form = $this->getLoginForm($request, new AdminUser(), $this->createForm(AdminUserLoginType::class));
        if ($form instanceof RedirectResponse) {
            return $form;
        }
        $arr["login_form"] = $form->createView();

        return $this->renderWithBackUrl(
            'backend/access/login.html.twig', $arr, $this->generateUrl("homepage")
        );
    }

    /**
     * @Route("/", name="backend_default")
     * @param Request $request
     * @return Response
     */
    public function defaultAction(Request $request)
    {
        return $this->redirectToRoute("backend_login");
    }

    /**
     * @Route("/login_check", name="backend_login_check")
     * @param Request $request
     */
    public function loginCheck(Request $request)
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="backend_logout")
     * @param Request $request
     */
    public function logoutAction(Request $request)
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}