<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Controller\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * @Security("has_role('ROLE_ADMIN')")
 */
class DashboardController extends BaseController
{
    /**
     * @Route("/", name="backend_dashboard_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startAction(Request $request)
    {
        $arr = [];
        $arr["organisations"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findAll();
        return $this->render("backend/dashboard/index.html.twig", $arr);
    }
}