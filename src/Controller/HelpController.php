<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/09/2017
 * Time: 08:50
 */

namespace App\Controller;

use App\Controller\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/help")
 * @Security("has_role('ROLE_USER')")
 */
class HelpController extends BaseController
{
    /**
     * @Route("/users", name="help_users")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction(Request $request)
    {
        //todo
        return $this->renderWithBackUrl(
            "event/assign.html.twig",
            [],
            $this->generateUrl("dashboard_index")
        );
    }

    /**
     * @Route("/admins", name="help_admins")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminsAction(Request $request)
    {
        //todo
        return $this->renderWithBackUrl(
            "event/assign.html.twig",
            [],
            $this->generateUrl("dashboard_index")
        );
    }
}