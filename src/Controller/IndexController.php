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
use App\Entity\Event;
use App\Model\Event\SearchModel;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
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
        //get the events from the next month
        $searchModel = new SearchModel(SearchModel::MONTH);
        $eventRepository = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepository->search($searchModel);

        return $this->render('index/index.html.twig', ['events' => $events]);
    }

    protected function getIndexBreadcrumbs()
    {
        return [];
    }
}