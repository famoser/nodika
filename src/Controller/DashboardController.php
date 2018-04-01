<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseDoctrineController;
use App\Entity\EventLine;
use App\Model\Event\SearchEventModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * @Security("has_role('ROLE_USER')")
 */
class DashboardController extends BaseDoctrineController
{
    /**
     * @Route("/", name="dashboard_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $searchModel = new SearchEventModel();
        $eventLineRepository = $this->getDoctrine()->getRepository(EventLine::class);

        $eventLineModels = $eventLineRepository->findEventLineModels($searchModel);
        $arr['event_line_models'] = $eventLineModels;

        return $this->render('dashboard/index.html.twig', $arr);
    }
}
