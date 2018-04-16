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
use App\Model\Event\SearchModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 * @Security("has_role('ROLE_USER')")
 */
class IndexController extends BaseDoctrineController
{
    /**
     * @Route("/", name="index_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $searchModel = new SearchModel();
        $eventLineRepository = $this->getDoctrine()->getRepository(EventLine::class);

        $eventLineModels = $eventLineRepository->findEventLineModels($searchModel);
        $arr['event_line_models'] = $eventLineModels;
        $arr["user"] = $this->getUser();

        return $this->render('index/index.html.twig', $arr);
    }
}
