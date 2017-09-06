<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * @Security("has_role('ROLE_USER')")
 */
class DashboardController extends BaseController
{
    /**
     * @Route("/", name="dashboard_index")
     */
    public function startAction(Request $request)
    {
        $arr = [];
        $arr["leading_organisations"] = $this->getPerson()->getLeaderOf();
        $arr["my_organisations"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findByPerson($this->getPerson());
        return $this->render("dashboard/start.html.twig", $arr);
    }
}