<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace App\Controller\Backend;

use App\Controller\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * @Security("has_role('ROLE_ADMIN')")
 */
class DashboardController extends BaseController
{
    /**
     * @Route("/", name="backend_dashboard_index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startAction()
    {
        $arr = [];
        $arr["organisations"] = $this->getDoctrine()->getRepository("App:Organisation")->findAll();
        return $this->renderWithBackUrl("backend/dashboard/index.html.twig", $arr, "this is the dashboard");
    }
}
