<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Controller\Api\Base\BaseApiController;
use App\Controller\Traits\EventControllerTrait;
use App\Entity\Event;
use App\Entity\EventPast;
use App\Entity\Setting;
use App\Enum\EventChangeType;
use App\Helper\DoctrineHelper;
use App\Model\Event\SearchModel;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/confirm')]
class ConfirmController extends BaseApiController
{
    use EventControllerTrait;

    /**
     * @throws \Exception
     */
    #[Route(path: '/events', name: 'api_confirm_events')]
    public function apiEvents(ManagerRegistry $registry, SerializerInterface $serializer): JsonResponse
    {
        // get all assignable events
        $settings = $registry->getRepository(Setting::class)->findSingle();

        $searchModel = new SearchModel(SearchModel::NONE);
        $searchModel->setClinics($this->getUser()->getClinics());
        $searchModel->setEndDateTime((new \DateTime())->add(new \DateInterval('P'.$settings->getCanConfirmDaysAdvance().'D')));
        $searchModel->setIsConfirmed(false);
        $events = $registry->getRepository(Event::class)->search($searchModel);

        $apiEvents = [];
        foreach ($events as $event) {
            if (null === $event->getDoctor() || $event->getDoctor()->getId() === $this->getUser()->getId()) {
                $apiEvents[] = $event;
            }
        }

        return $this->returnEvents($serializer, $apiEvents);
    }

    #[Route(path: '/event/{event}', name: 'api_confirm_event')]
    public function apiConfirm(Event $event, ManagerRegistry $registry): Response
    {
        // either assigned to this user or of a clinic the user is part of
        if (null !== $event->getDoctor() && $event->getDoctor()->getId() === $this->getUser()->getId()
            || $this->getUser()->getClinics()->contains($event->getClinic())) {
            $event->confirm($this->getUser());
            $eventPast = EventPast::create($event, EventChangeType::CONFIRMED, $this->getUser());
            DoctrineHelper::persistAndFlush($registry, ...[$event, $eventPast]);

            return new Response('ACK');
        }

        return new Response('NACK');
    }
}
