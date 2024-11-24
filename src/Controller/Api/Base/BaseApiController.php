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

use App\Controller\Base\BaseController;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class BaseApiController extends BaseController
{
    /**
     * @param Event[]|Event $events
     *
     * @return JsonResponse
     */
    protected function returnEvents(SerializerInterface $serializer, $events)
    {
        $data = $serializer->serialize($events, 'json', ['attributes' => ['id', 'startDateTime', 'endDateTime', 'clinic' => ['id', 'name'], 'doctor' => ['id', 'fullName'], 'eventTags' => ['name', 'colorText']]]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @param Doctor[]|Doctor $doctors
     *
     * @return JsonResponse
     */
    protected function returnDoctors(SerializerInterface $serializer, $doctors)
    {
        $data = $serializer->serialize($doctors, 'json', ['attributes' => ['id', 'fullName', 'clinics' => ['id', 'name']]]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @param Clinic[]|Clinic $clinics
     *
     * @return JsonResponse
     */
    protected function returnClinics(SerializerInterface $serializer, $clinics)
    {
        $data = $serializer->serialize($clinics, 'json', ['attributes' => ['id', 'name', 'doctors' => ['id', 'fullName']]]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
