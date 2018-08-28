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
use App\Entity\EventOffer;
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

        //get open offers & determine which need actions
        $offers = $this->getDoctrine()->getRepository(EventOffer::class)->findBy(['isResolved' => false]);
        /** @var EventOffer[] $actingOffers */
        $actingOffers = [];
        foreach ($offers as $offer) {
            if (EventOffer::NONE !== $offer->getPendingAction($this->getUser())) {
                $actingOffers[] = $offer;
            }
        }

        return $this->render('index/index.html.twig', ['events' => $events, 'offers' => $actingOffers]);
    }

    protected function getIndexBreadcrumbs()
    {
        return [];
    }
}
