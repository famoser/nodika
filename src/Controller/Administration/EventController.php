<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration;

use App\Controller\Administration\Base\BaseApiController;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\EventGeneration;
use App\Entity\EventGenerationDateException;
use App\Entity\EventGenerationTargetClinic;
use App\Entity\EventGenerationTargetDoctor;
use App\Entity\EventPast;
use App\Entity\EventTag;
use App\Enum\EventChangeType;
use App\Enum\EventTagType;
use App\Enum\EventType;
use App\Form\Event\RemoveType;
use App\Helper\DoctrineHelper;
use App\Model\Breadcrumb;
use App\Service\Interfaces\EventGenerationServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/events')]
class EventController extends BaseApiController
{
    /**
     * @return Response
     */
    #[Route(path: '/new', name: 'administration_event_new')]
    public function new(Request $request, TranslatorInterface $translator, ManagerRegistry $registry)
    {
        $event = new Event();
        $myForm = $this->handleCreateForm(
            $request,
            $event,
            function ($manager) use ($event, $translator): bool {
                if (!$this->ensureValidDoctorClinicPair($event, $translator)) {
                    return false;
                }

                /** @var ObjectManager $manager */
                $eventPast = EventPast::create($event, EventChangeType::CREATED, $this->getUser());
                $manager->persist($eventPast);

                return true;
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event/new.html.twig', $arr);
    }

    /**
     * @return Response
     */
    #[Route(path: '/{event}/edit', name: 'administration_event_edit')]
    public function edit(Request $request, Event $event, TranslatorInterface $translator, ManagerRegistry $registry)
    {
        $myForm = $this->handleUpdateForm(
            $request,
            $event,
            function ($manager) use ($event, $translator): bool {
                if (!$this->ensureValidDoctorClinicPair($event, $translator)) {
                    return false;
                }

                /* @var ObjectManager $manager */
                $eventPast = EventPast::create($event, EventChangeType::CHANGED, $this->getUser());
                $manager->persist($eventPast);

                return true;
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event/edit.html.twig', $arr);
    }

    private function ensureValidDoctorClinicPair(Event $event, TranslatorInterface $translator): bool
    {
        if (null === $event->getDoctor() || $event->getDoctor()->getClinics()->contains($event->getClinic())) {
            return true;
        }
        $this->displayError($translator->trans('edit.error.doctor_not_part_of_clinic', [], 'administration_event'));

        return false;
    }

    /**
     * @return Response
     */
    #[Route(path: '/{event}/remove', name: 'administration_event_remove')]
    public function remove(Request $request, Event $event, TranslatorInterface $translator, ManagerRegistry $registry)
    {
        $myForm = $this->handleForm(
            $this->createForm(RemoveType::class, $event)
                ->add('remove', SubmitType::class, ['translation_domain' => 'common_form', 'label' => 'submit.delete']),
            $request,
            function () use ($event, $translator, $registry): RedirectResponse {
                /* @var FormInterface $form */
                $event->delete();
                $eventPast = EventPast::create($event, EventChangeType::REMOVED, $this->getUser());

                DoctrineHelper::persistAndFlush($registry, ...[$eventPast, $event]);

                $this->displaySuccess($translator->trans('successful.delete', [], 'common_form'));

                return $this->redirectToRoute('administration_events');
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event/remove.html.twig', $arr);
    }

    #[Route(path: '/{event}/history', name: 'administration_event_history')]
    public function history(Event $event): Response
    {
        $arr['event'] = $event;

        return $this->render('administration/event/history.html.twig', $arr);
    }

    #[Route(path: '/generations', name: 'administration_event_generations')]
    public function generate(ManagerRegistry $registry): Response
    {
        $generations = $registry->getRepository(EventGeneration::class)->findAll();
        $arr['generations'] = $generations;

        return $this->render('administration/event/generations.html.twig', $arr);
    }

    /**
     * @return Response
     *
     * @throws \Exception
     */
    #[Route(path: '/generation/new/{tagType}', name: 'administration_event_generation_new')]
    public function generateNew(int $tagType, EventGenerationServiceInterface $eventGenerationService, TranslatorInterface $translator, ManagerRegistry $registry): RedirectResponse
    {
        // get to be assigned tag
        $tag = $registry->getRepository(EventTag::class)->findOneBy(['tagType' => $tagType]);
        if (!($tag instanceof EventTag)) {
            return $this->redirectToRoute('administration_event_generations');
        }

        // create our new generation
        $generation = new EventGeneration();
        $generation->getAssignEventTags()->add($tag);
        $generation->registerChangeBy($this->getUser());

        // try to retrieve last generation of that type
        $lastGenerations = $registry->getRepository(EventGeneration::class)->findBy(['applied' => true], ['lastChangedAt' => 'DESC']);
        $lastGeneration = null;
        foreach ($lastGenerations as $lastGenerationLoop) {
            if ($lastGenerationLoop->getAssignEventTags()->contains($tag)) {
                $lastGeneration = $lastGenerationLoop;
                break;
            }
        }

        // transfer props if previous generation exists
        if (null !== $lastGeneration) {
            // precalculate some time diffs; round up when looking at years
            $lastLength = $lastGeneration->getStartDateTime()->diff($lastGeneration->getEndDateTime());
            $yearDifference = new \DateInterval('P'.($lastLength->y + ($lastLength->d > 240 ? 1 : 0)).'Y');

            // set name
            $generation->setName('Re: '.$lastGeneration->getName());

            // copy start/end
            $generation->setStartDateTime(clone $lastGeneration->getEndDateTime());
            $generation->setEndDateTime(clone ($lastGeneration->getEndDateTime())->add($lastLength));
            $generation->setStartCronExpression($lastGeneration->getStartCronExpression());
            $generation->setEndCronExpression($lastGeneration->getEndCronExpression());

            // copy participants
            foreach ($lastGeneration->getClinics() as $clinicTarget) {
                if (null === $clinicTarget->getClinic()->getDeletedAt()) {
                    $newClinic = new EventGenerationTargetClinic();
                    $newClinic->setClinic($clinicTarget->getClinic());
                    $newClinic->setDefaultOrder($clinicTarget->getDefaultOrder());
                    $newClinic->setWeight($clinicTarget->getWeight());
                    $newClinic->setEventGeneration($generation);
                    $generation->getClinics()->add($newClinic);
                }
            }
            foreach ($lastGeneration->getDoctors() as $doctorTarget) {
                if (null === $doctorTarget->getDoctor()->getDeletedAt()) {
                    $newDoctor = new EventGenerationTargetDoctor();
                    $newDoctor->setDoctor($doctorTarget->getDoctor());
                    $newDoctor->setDefaultOrder($doctorTarget->getDefaultOrder());
                    $newDoctor->setWeight($doctorTarget->getWeight());
                    $newDoctor->setEventGeneration($generation);
                    $generation->getDoctors()->add($newDoctor);
                }
            }

            // copy settings
            $generation->setDifferentiateByEventType($lastGeneration->getDifferentiateByEventType());
            $generation->setWeekdayWeight($lastGeneration->getWeekdayWeight());
            $generation->setSaturdayWeight($lastGeneration->getSaturdayWeight());
            $generation->setSundayWeight($lastGeneration->getSundayWeight());
            $generation->setHolidayWeight($lastGeneration->getHolidayWeight());
            foreach ($lastGeneration->getConflictEventTags() as $conflictEventTag) {
                if (null === $conflictEventTag->getDeletedAt()) {
                    $generation->getConflictEventTags()->add($conflictEventTag);
                }
            }
            foreach ($lastGeneration->getDateExceptions() as $dateException) {
                $newException = new EventGenerationDateException();
                $newException->setEventType($dateException->getEventType());
                $newException->setEventGeneration($generation);
                $newException->setStartDateTime((clone $dateException->getStartDateTime())->add($yearDifference));
                $newException->setEndDateTime((clone $dateException->getEndDateTime())->add($yearDifference));
                $generation->getDateExceptions()->add($newException);
            }
        } else {
            // set default name
            $generation->setName(
                $translator->trans('entity.name', [], 'entity_event_generation').' '.
                EventTagType::getTranslation($tag->getTagType(), $translator).' '
            );

            // set start/end
            $generation->setStartDateTime(new \DateTime());
            $generation->setEndDateTime((new \DateTime())->add(new \DateInterval('P1Y')));

            // set participants
            $clinics = $registry->getRepository(Clinic::class)->findBy(['deletedAt' => null]);
            foreach ($clinics as $clinic) {
                $targetClinic = new EventGenerationTargetClinic();
                $targetClinic->setEventGeneration($generation);
                $targetClinic->setClinic($clinic);
                $generation->getClinics()->add($targetClinic);
            }

            // set other sensible defaults
            if (EventTagType::ACTIVE_SERVICE === $tag->getTagType()) {
                $generation->setDifferentiateByEventType(true);
                $generation->setStartCronExpression('0 8 * * *');
                $generation->setEndCronExpression('0 8 * * *');
                $generation->setConflictBufferInEventMultiples(1.0);
            } elseif (EventTagType::BACKUP_SERVICE === $tag->getTagType()) {
                $generation->setDifferentiateByEventType(false);
                $generation->setStartCronExpression('0 8 */7 * *');
                $generation->setEndCronExpression('0 8 */7 * *');
                $generation->setConflictBufferInEventMultiples(1.0 / 7);
            }
            foreach ($registry->getRepository(EventTag::class)->findBy(['deletedAt' => null]) as $otherTag) {
                $generation->getConflictEventTags()->add($otherTag);
            }
            // add yearly holidays
            $yearlyHolidays = ['01-01', '01-02.', '08-01.', '12-31'];
            $currentIndex = 0;
            $currentYear = $generation->getStartDateTime()->format('Y');
            while (true) {
                $exceptionDateString = $currentYear.'-'.$yearlyHolidays[$currentIndex];
                $exceptionDate = new \DateTime($exceptionDateString);

                // break if can not be inside generation anymore
                if ($exceptionDate > $generation->getEndDateTime()) {
                    break;
                }

                // add if inside generation
                if ($exceptionDate > $generation->getStartDateTime()) {
                    $newException = new EventGenerationDateException();
                    $newException->setEventType(EventType::HOLIDAY);
                    $newException->setEventGeneration($generation);
                    $newException->setStartDateTime($exceptionDate);
                    $newException->setEndDateTime(new \DateTime($exceptionDateString.' 23:59'));
                    $generation->getDateExceptions()->add($newException);
                }

                // update index/current year
                ++$currentIndex;
                if ($currentIndex === \count($yearlyHolidays)) {
                    $currentIndex = 0;
                    ++$currentYear;
                }
            }
        }

        // save
        $eventGenerationService->generate($generation);
        DoctrineHelper::persistAndFlush($registry, ...[$generation]);

        return $this->redirectToRoute('administration_event_generation', ['generation' => $generation->getId()]);
    }

    #[Route(path: '/generation/{generation}', name: 'administration_event_generation')]
    public function generation(EventGeneration $generation): Response
    {
        return $this->render('administration/event/generation.html.twig', ['generation' => $generation]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/generation/{generation}/get', name: 'administration_event_generation_get')]
    public function generationGet(SerializerInterface $serializer, EventGeneration $generation)
    {
        return $this->returnGeneration($serializer, $generation);
    }

    /**
     * @return Response
     */
    #[Route(path: '/generation/{generation}/update', name: 'administration_event_generation_update')]
    public function generationUpdate(Request $request, SerializerInterface $serializer, EventGeneration $generation, EventGenerationServiceInterface $eventGenerationService, ManagerRegistry $registry): JsonResponse
    {
        // only update if not applied yet
        if ($generation->getIsApplied()) {
            throw new AccessDeniedHttpException();
        }

        $manager = $registry->getManager();

        // prepare content & request
        $content = json_decode($request->getContent(), true);
        if (\is_array($content)) {
            // write simple props first
            $allowedProps = [
                'name' => 1, 'startCronExpression' => 1, 'endCronExpression' => 1,
                'startDateTime' => 2, 'endDateTime' => 2,
                'differentiateByEventType' => 3, 'mindPreviousEvents' => 3,
                'weekdayWeight' => 4, 'saturdayWeight' => 4, 'sundayWeight' => 4, 'holydayWeight' => 4,
                'step' => 5,
            ];
            foreach ($allowedProps as $prop => $valueType) {
                if (\array_key_exists($prop, $content)) {
                    // convert type
                    $value = $content[$prop];
                    switch ($valueType) {
                        case 2:
                            $value = new \DateTime($value);
                            break;
                        case 3:
                            $value = true === $value || 'true' === $value;
                            break;
                        case 4:
                            $value = (float) $value;
                            break;
                        case 5:
                            $value = (int) $value;
                            break;
                        default:
                            break;
                    }
                    $setterName = 'set'.mb_strtoupper(mb_substr($prop, 0, 1)).mb_substr($prop, 1);
                    $generation->$setterName($value);
                }
            }

            // refresh dependencies
            if (\array_key_exists('conflictEventTagIds', $content)) {
                $eventTagIds = $content['conflictEventTagIds'];
                $eventTags = $registry->getRepository(EventTag::class)->findBy(['id' => $eventTagIds]);
                $generation->getConflictEventTags()->clear();
                foreach ($eventTags as $eventTag) {
                    $generation->getConflictEventTags()->add($eventTag);
                }
            }
            if (\array_key_exists('assignEventTagIds', $content)) {
                $eventTagIds = $content['assignEventTagIds'];
                $eventTags = $registry->getRepository(EventTag::class)->findBy(['id' => $eventTagIds]);
                $generation->getAssignEventTags()->clear();
                foreach ($eventTags as $eventTag) {
                    $generation->getAssignEventTags()->add($eventTag);
                }
            }
            if (\array_key_exists('dateExceptions', $content)) {
                $dateExceptions = $content['dateExceptions'];
                $generation->getDateExceptions()->clear();
                foreach ($dateExceptions as $dateException) {
                    $exception = new EventGenerationDateException();
                    $exception->setStartDateTime(new \DateTime($dateException['startDateTime']));
                    $exception->setEndDateTime(new \DateTime($dateException['endDateTime']));
                    $exception->setEventGeneration($generation);
                    $exception->setEventType($dateException['eventType']);
                    $generation->getDateExceptions()->add($exception);
                    $manager->persist($exception);
                }
            }
            if (\array_key_exists('targetClinics', $content)) {
                // get key/value of clinics
                $clinics = $registry->getRepository(Clinic::class)->findAll();
                $clinicById = [];
                foreach ($clinics as $clinic) {
                    $clinicById[$clinic->getId()] = $clinic;
                }

                // create target clinics
                $targetClinics = $content['targetClinics'];
                $generation->getClinics()->clear();
                foreach ($targetClinics as $targetClinicArr) {
                    $targetClinic = new EventGenerationTargetClinic();
                    $targetClinic->setClinic($clinicById[$targetClinicArr['id']]);
                    $targetClinic->setDefaultOrder($targetClinicArr['defaultOrder']);
                    $targetClinic->setWeight($targetClinicArr['weight']);
                    $targetClinic->setEventGeneration($generation);
                    $generation->getClinics()->add($targetClinic);
                    $manager->persist($targetClinic);
                }
            }
            if (\array_key_exists('targetDoctors', $content)) {
                // get key/value of doctors
                $doctors = $registry->getRepository(Doctor::class)->findAll();
                $doctorById = [];
                foreach ($doctors as $doctor) {
                    $doctorById[$doctor->getId()] = $doctor;
                }

                // create target doctors
                $targetDoctors = $content['targetDoctors'];
                $generation->getDoctors()->clear();
                foreach ($targetDoctors as $targetDoctorArr) {
                    $targetDoctor = new EventGenerationTargetDoctor();
                    $targetDoctor->setDoctor($doctorById[$targetDoctorArr['id']]);
                    $targetDoctor->setDefaultOrder($targetDoctorArr['defaultOrder']);
                    $targetDoctor->setWeight($targetDoctorArr['weight']);
                    $targetDoctor->setEventGeneration($generation);
                    $generation->getDoctors()->add($targetDoctor);
                    $manager->persist($targetDoctor);
                }
            }
        }

        // regenerate events
        $eventGenerationService->generate($generation);

        // save changes
        $manager->persist($generation);
        $manager->flush();

        return $this->returnGeneration($serializer, $generation);
    }

    /**
     * @return Response
     */
    #[Route(path: '/generation/{generation}/targets', name: 'administration_event_generation_targets')]
    public function generationTargets(SerializerInterface $serializer, ManagerRegistry $registry)
    {
        $doctors = $registry->getRepository(Doctor::class)->findBy(['deletedAt' => null]);
        $clinics = $registry->getRepository(Clinic::class)->findBy(['deletedAt' => null]);

        return $this->returnTargets($serializer, $doctors, $clinics);
    }

    /**
     * @return Response
     */
    #[Route(path: '/generation/{generation}/apply', name: 'administration_event_generation_apply')]
    public function generateApply(EventGeneration $generation, EventGenerationServiceInterface $eventGenerationService)
    {
        $eventGenerationService->persist($generation, $this->getUser());

        return $this->returnOk();
    }

    /**
     * @return Response
     */
    #[Route(path: '/{event}/toggle_confirm', name: 'administration_event_toggle_confirm')]
    public function toggleConfirm(Event $event, ManagerRegistry $registry): RedirectResponse
    {
        if ($event->isConfirmed()) {
            $event->undoConfirm();
        } else {
            $event->confirm($this->getUser());
        }

        $eventPast = EventPast::create($event, EventChangeType::CHANGED, $this->getUser());
        DoctrineHelper::persistAndFlush($registry, ...[$event, $eventPast]);

        return $this->redirectToRoute('administration_events');
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs(): array
    {
        return array_merge(parent::getIndexBreadcrumbs(), [
            new Breadcrumb(
                $this->generateUrl('administration_events'),
                $this->getTranslator()->trans('events.title', [], 'administration')
            ),
        ]);
    }
}
