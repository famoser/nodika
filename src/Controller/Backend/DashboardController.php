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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startAction()
    {
        $arr = [];
        $arr['organisations'] = $this->getDoctrine()->getRepository('App:Organisation')->findAll();

        return $this->renderWithBackUrl('backend/dashboard/index.html.twig', $arr, 'this is the dashboard');
    }
}
