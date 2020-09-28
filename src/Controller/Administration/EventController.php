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
use App\Model\Breadcrumb;
use App\Service\Interfaces\EventGenerationServiceInterface;
use Doctrine\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/events")
 */
class EventController extends BaseApiController
{
    /**
     * @Route("/new", name="administration_event_new")
     *
     * @return Response
     */
    public function newAction(Request $request, TranslatorInterface $translator)
    {
        $event = new Event();
        $myForm = $this->handleCreateForm(
            $request,
            $event,
            function ($manager) use ($event, $translator) {
                if (!$this->ensureValidDoctorClinicPair($event, $translator)) {
                    return false;
                }

                /* @var ObjectManager $manager */
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
     * @Route("/{event}/edit", name="administration_event_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, Event $event, TranslatorInterface $translator)
    {
        $myForm = $this->handleUpdateForm(
            $request,
            $event,
            function ($manager) use ($event, $translator) {
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

    /**
     * @return bool
     */
    private function ensureValidDoctorClinicPair(Event $event, TranslatorInterface $translator)
    {
        if (null === $event->getDoctor() || $event->getDoctor()->getClinics()->contains($event->getClinic())) {
            return true;
        }
        $this->displayError($translator->trans('edit.error.doctor_not_part_of_clinic', [], 'administration_event'));

        return false;
    }

    /**
     * @Route("/{event}/remove", name="administration_event_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, Event $event, TranslatorInterface $translator)
    {
        $myForm = $this->handleForm(
            $this->createForm(RemoveType::class, $event)
                ->add('remove', SubmitType::class, ['translation_domain' => 'common_form', 'label' => 'submit.delete']),
            $request,
            function () use ($event, $translator) {
                /* @var FormInterface $form */
                $event->delete();
                $eventPast = EventPast::create($event, EventChangeType::REMOVED, $this->getUser());

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($eventPast);
                $manager->persist($event);

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

    /**
     * @Route("/{event}/history", name="administration_event_history")
     *
     * @return Response
     */
    public function historyAction(Event $event)
    {
        $arr['event'] = $event;

        return $this->render('administration/event/history.html.twig', $arr);
    }

    /**
     * @Route("/generations", name="administration_event_generations")
     *
     * @return Response
     */
    public function generateAction()
    {
        $generations = $this->getDoctrine()->getRepository(EventGeneration::class)->findAll();
        $arr['generations'] = $generations;

        return $this->render('administration/event/generations.html.twig', $arr);
    }

    /**
     * @Route("/generation/new/{tagType}", name="administration_event_generation_new")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function generateNewAction(int $tagType, EventGenerationServiceInterface $eventGenerationService, TranslatorInterface $translator)
    {
        //get to be assigned tag
        $tag = $this->getDoctrine()->getRepository(EventTag::class)->findOneBy(['tagType' => $tagType]);
        if (!($tag instanceof EventTag)) {
            return $this->redirectToRoute('administration_event_generations');
        }

        //create our new generation
        $generation = new EventGeneration();
        $generation->getAssignEventTags()->add($tag);
        $generation->registerChangeBy($this->getUser());

        //try to retrieve last generation of that type
        $lastGenerations = $this->getDoctrine()->getRepository(EventGeneration::class)->findBy(['applied' => true], ['lastChangedAt' => 'DESC']);
        $lastGeneration = null;
        foreach ($lastGenerations as $lastGenerationLoop) {
            if ($lastGenerationLoop->getAssignEventTags()->contains($tag)) {
                $lastGeneration = $lastGenerationLoop;
                break;
            }
        }

        //transfer props if previous generation exists
        if (null !== $lastGeneration) {
            //precalculate some time diffs; round up when looking at years
            $lastLength = $lastGeneration->getStartDateTime()->diff($lastGeneration->getEndDateTime());
            $yearDifference = new \DateInterval('P'.($lastLength->y + ($lastLength->d > 240 ? 1 : 0)).'Y');

            //set name
            $generation->setName('Re: '.$lastGeneration->getName());

            //copy start/end
            $generation->setStartDateTime(clone $lastGeneration->getEndDateTime());
            $generation->setEndDateTime(clone ($lastGeneration->getEndDateTime())->add($lastLength));
            $generation->setStartCronExpression($lastGeneration->getStartCronExpression());
            $generation->setEndCronExpression($lastGeneration->getEndCronExpression());

            //copy participants
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

            //copy settings
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
            //set default name
            $generation->setName(
                $translator->trans('entity.name', [], 'entity_event_generation').' '.
                EventTagType::getTranslation($tag->getTagType(), $translator).' '
            );

            //set start/end
            $generation->setStartDateTime(new \DateTime());
            $generation->setEndDateTime((new \DateTime())->add(new \DateInterval('P1Y')));

            //set participants
            $clinics = $this->getDoctrine()->getRepository(Clinic::class)->findBy(['deletedAt' => null]);
            foreach ($clinics as $clinic) {
                $targetClinic = new EventGenerationTargetClinic();
                $targetClinic->setEventGeneration($generation);
                $targetClinic->setClinic($clinic);
                $generation->getClinics()->add($targetClinic);
            }

            //set other sensible defaults
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
            foreach ($this->getDoctrine()->getRepository(EventTag::class)->findBy(['deletedAt' => null]) as $otherTag) {
                $generation->getConflictEventTags()->add($otherTag);
            }
            //add yearly holidays
            $yearlyHolidays = ['01-01', '01-02.', '08-01.', '12-31'];
            $currentIndex = 0;
            $currentYear = $generation->getStartDateTime()->format('Y');
            while (true) {
                $exceptionDateString = $currentYear.'-'.$yearlyHolidays[$currentIndex];
                $exceptionDate = new \DateTime($exceptionDateString);

                //break if can not be inside generation anymore
                if ($exceptionDate > $generation->getEndDateTime()) {
                    break;
                }

                //add if inside generation
                if ($exceptionDate > $generation->getStartDateTime()) {
                    $newException = new EventGenerationDateException();
                    $newException->setEventType(EventType::HOLIDAY);
                    $newException->setEventGeneration($generation);
                    $newException->setStartDateTime($exceptionDate);
                    $newException->setEndDateTime(new \DateTime($exceptionDateString.' 23:59'));
                    $generation->getDateExceptions()->add($newException);
                }

                //update index/current year
                ++$currentIndex;
                if ($currentIndex === \count($yearlyHolidays)) {
                    $currentIndex = 0;
                    ++$currentYear;
                }
            }
        }

        //save
        $eventGenerationService->generate($generation);
        $this->fastSave($generation);

        return $this->redirectToRoute('administration_event_generation', ['generation' => $generation->getId()]);
    }

    /**
     * @Route("/generation/{generation}", name="administration_event_generation")
     *
     * @return Response
     */
    public function generationAction(EventGeneration $generation)
    {
        return $this->render('administration/event/generation.html.twig', ['generation' => $generation]);
    }

    /**
     * @Route("/generation/{generation}/get", name="administration_event_generation_get")
     *
     * @return Response
     */
    public function generationGetAction(EventGeneration $generation)
    {
        return $this->returnGeneration($generation);
    }

    /**
     * @Route("/generation/{generation}/update", name="administration_event_generation_update")
     *
     * @return Response
     */
    public function generationUpdateAction(Request $request, EventGeneration $generation, EventGenerationServiceInterface $eventGenerationService)
    {
        //only update if not applied yet
        if ($generation->getIsApplied()) {
            throw new AccessDeniedHttpException();
        }

        $manager = $this->getDoctrine()->getManager();

        //prepare content & request
        $content = json_decode($request->getContent(), true);
        if (\is_array($content)) {
            //write simple props first
            $allowedProps = [
                'name' => 1, 'startCronExpression' => 1, 'endCronExpression' => 1,
                'startDateTime' => 2, 'endDateTime' => 2,
                'differentiateByEventType' => 3, 'mindPreviousEvents' => 3,
                'weekdayWeight' => 4, 'saturdayWeight' => 4, 'sundayWeight' => 4, 'holydayWeight' => 4,
                'step' => 5,
            ];
            foreach ($allowedProps as $prop => $valueType) {
                if (\array_key_exists($prop, $content)) {
                    //convert type
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

            //refresh dependencies
            if (\array_key_exists('conflictEventTagIds', $content)) {
                $eventTagIds = $content['conflictEventTagIds'];
                $eventTags = $this->getDoctrine()->getRepository(EventTag::class)->findBy(['id' => $eventTagIds]);
                $generation->getConflictEventTags()->clear();
                foreach ($eventTags as $eventTag) {
                    $generation->getConflictEventTags()->add($eventTag);
                }
            }
            if (\array_key_exists('assignEventTagIds', $content)) {
                $eventTagIds = $content['assignEventTagIds'];
                $eventTags = $this->getDoctrine()->getRepository(EventTag::class)->findBy(['id' => $eventTagIds]);
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
                //get key/value of clinics
                $clinics = $this->getDoctrine()->getRepository(Clinic::class)->findAll();
                $clinicById = [];
                foreach ($clinics as $clinic) {
                    $clinicById[$clinic->getId()] = $clinic;
                }

                //create target clinics
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
                //get key/value of doctors
                $doctors = $this->getDoctrine()->getRepository(Doctor::class)->findAll();
                $doctorById = [];
                foreach ($doctors as $doctor) {
                    $doctorById[$doctor->getId()] = $doctor;
                }

                //create target doctors
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

        //regenerate events
        $eventGenerationService->generate($generation);

        //save changes
        $manager->persist($generation);
        $manager->flush();

        return $this->returnGeneration($generation);
    }

    /**
     * @Route("/generation/{generation}/targets", name="administration_event_generation_targets")
     *
     * @return Response
     */
    public function generationTargets()
    {
        $doctors = $this->getDoctrine()->getRepository(Doctor::class)->findBy(['deletedAt' => null]);
        $clinics = $this->getDoctrine()->getRepository(Clinic::class)->findBy(['deletedAt' => null]);

        return $this->returnTargets($doctors, $clinics);
    }

    /**
     * @Route("/generation/{generation}/apply", name="administration_event_generation_apply")
     *
     * @return Response
     */
    public function generateApply(EventGeneration $generation, EventGenerationServiceInterface $eventGenerationService)
    {
        $eventGenerationService->persist($generation, $this->getUser());

        return $this->returnOk();
    }

    /**
     * @Route("/{event}/toggle_confirm", name="administration_event_toggle_confirm")
     *
     * @return Response
     */
    public function toggleConfirm(Event $event)
    {
        if ($event->isConfirmed()) {
            $event->undoConfirm();
        } else {
            $event->confirm($this->getUser());
        }

        $eventPast = EventPast::create($event, EventChangeType::CHANGED, $this->getUser());
        $this->fastSave($event, $eventPast);

        return $this->redirectToRoute('administration_events');
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return array_merge(parent::getIndexBreadcrumbs(), [
            new Breadcrumb(
                $this->generateUrl('administration_events'),
                $this->getTranslator()->trans('events.title', [], 'administration')
            ),
        ]);
    }
}
