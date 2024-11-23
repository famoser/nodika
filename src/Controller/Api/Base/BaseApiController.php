<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\Base;

use App\Controller\Base\BaseDoctrineController;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class BaseApiController extends BaseDoctrineController
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() +
            [
                'serializer' => SerializerInterface::class,
            ];
    }

    /**
     * @return SerializerInterface
     */
    private function getSerializer()
    {
        return $this->get('serializer');
    }

    /**
     * @param Event[]|Event $events
     *
     * @return JsonResponse
     */
    protected function returnEvents($events)
    {
        return new JsonResponse(
            $this->getSerializer()->serialize(
                $events,
                'json',
                ['attributes' => ['id', 'startDateTime', 'endDateTime', 'clinic' => ['id', 'name'], 'doctor' => ['id', 'fullName'], 'eventTags' => ['name', 'colorText']]]
            ),
            \Symfony\Component\HttpFoundation\Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @param Doctor[]|Doctor $doctors
     *
     * @return JsonResponse
     */
    protected function returnDoctors($doctors)
    {
        return new JsonResponse(
            $this->getSerializer()->serialize(
                $doctors,
                'json', ['attributes' => ['id', 'fullName', 'clinics' => ['id', 'name']]]
            ),
            \Symfony\Component\HttpFoundation\Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @param Clinic[]|Clinic $clinics
     *
     * @return JsonResponse
     */
    protected function returnClinics($clinics)
    {
        return new JsonResponse(
            $this->getSerializer()->serialize(
                $clinics,
                'json', ['attributes' => ['id', 'name', 'doctors' => ['id', 'fullName']]]
            ),
            \Symfony\Component\HttpFoundation\Response::HTTP_OK,
            [],
            true
        );
    }
}
