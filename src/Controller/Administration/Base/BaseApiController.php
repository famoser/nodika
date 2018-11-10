<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Base;

use App\Api\Dto\GenerationTargetsDto;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\EventGeneration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class BaseApiController extends BaseController
{
    public static function getSubscribedServices()
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

    protected function returnOk()
    {
        return new Response('', 204);
    }

    /**
     * @param EventGeneration $generation
     *
     * @return JsonResponse
     */
    protected function returnGeneration($generation)
    {
        return new JsonResponse(
            $this->getSerializer()->serialize(
                $generation,
                'json',
                ['attributes' => ['name', 'startDateTime', 'endDateTime', 'startCronExpression', 'endCronExpression', 'differentiateByEventType',
                    'weekdayWeight', 'saturdayWeight', 'sundayWeight', 'holydayWeight', 'mindPreviousEvents', 'applied', 'step',
                    'conflictEventTags' => ['id', 'name'], 'assignEventTags' => ['id', 'name'], 'dateExceptions' => ['id', 'startDateTime', 'endDateTime', 'eventType'],
                    'doctors' => ['weight', 'generationScore', 'doctor' => ['id', 'fullName']], 'clinics' => ['weight', 'generationScore', 'clinic' => ['id', 'name']],
                    'previewEvents' => ['startDateTime', 'endDateTime', 'eventType', 'clinic' => ['id', 'name'], 'doctor' => ['id', 'fullName']], ]]
            ),
            200,
            [],
            true
        );
    }

    /**     *
     * @param Doctor[] $doctors
     * @param Clinic[] $clinics
     *
     * @return JsonResponse
     */
    protected function returnTargets($doctors, $clinics)
    {
        $obj = new GenerationTargetsDto($doctors, $clinics);

        return new JsonResponse(
            $this->getSerializer()->serialize(
                $obj,
                'json',
                ['attributes' => ['doctors' => ['id', 'fullName'], 'clinics' => ['id', 'name']]]
            ),
            200,
            [],
            true
        );
    }
}
