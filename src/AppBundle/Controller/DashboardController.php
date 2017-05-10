<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/start")
 */
class DashboardController extends BaseController
{
    /**
     * @Route("/", name="dashboard_start")
     */
    public function newAction(Request $request)
    {
        $arr = [];

        return $this->render("dashboard/start.html.twig", $arr);
    }
}