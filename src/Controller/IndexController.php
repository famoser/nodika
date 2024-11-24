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

use App\Controller\Base\BaseController;
use App\Entity\Event;
use App\Entity\EventOffer;
use App\Model\Event\SearchModel;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/')]
class IndexController extends BaseController
{
    #[Route(path: '/', name: 'index_index')]
    public function index(ManagerRegistry $registry): Response
    {
        // get the events from the next month
        $searchModel = new SearchModel(SearchModel::MONTH);
        $eventRepository = $registry->getRepository(Event::class);
        $events = $eventRepository->search($searchModel);

        // get open offers & determine which need actions
        $offers = $registry->getRepository(EventOffer::class)->findBy(['isResolved' => false]);
        /** @var EventOffer[] $actingOffers */
        $actingOffers = [];
        foreach ($offers as $offer) {
            if (EventOffer::NONE !== $offer->getPendingAction($this->getUser())) {
                $actingOffers[] = $offer;
            }
        }

        return $this->render('index/index.html.twig', ['events' => $events, 'offers' => $actingOffers]);
    }

    protected function getIndexBreadcrumbs(): array
    {
        return [];
    }
}
