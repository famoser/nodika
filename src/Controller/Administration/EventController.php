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
use App\Form\Event\RemoveType;
use App\Model\Breadcrumb;
use App\Service\Interfaces\EventGenerationServiceInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/events")
 */
class EventController extends BaseApiController
{
    /**
     * @Route("/new", name="administration_event_new")
     *
     * @param Request $request
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
     * @param Request $request
     * @param Event   $event
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
     * @param Event               $event
     * @param TranslatorInterface $translator
     *
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
     * @param Request $request
     * @param Event   $event
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
     * @param Event $event
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
     * @param int                 $tagType
     * @param TranslatorInterface $translator
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function generateNewAction(int $tagType, TranslatorInterface $translator)
    {
        //get to be assigned tag
        $tag = $this->getDoctrine()->getRepository(EventTag::class)->findOneBy(['tagType' => $tagType]);
        if (!($tag instanceof EventTag)) {
            return $this->redirectToRoute('administration_event_generations');
        }

        $generation = new EventGeneration();
        $generation->getAssignEventTags()->add($tag);
        $generation->registerChangeBy($this->getUser());

        //set other tags to prevent conflict with
        $otherTags = $this->getDoctrine()->getRepository(EventTag::class)->findAll();
        foreach ($otherTags as $otherTag) {
            $generation->getConflictEventTags()->add($otherTag);
        }

        //set "sensible" default name
        $generation->setName(
            $translator->trans('entity.name', [], 'entity_event_generation').' '.
            EventTagType::getTranslation($tag->getTagType(), $translator).' '
        );

        //set settings depending on chosen template
        if (EventTagType::ACTIVE_SERVICE === $tag->getTagType()) {
            $generation->setDifferentiateByEventType(true);
        } elseif (EventTagType::BACKUP_SERVICE === $tag->getTagType()) {
            $generation->setDifferentiateByEventType(false);
        }

        //set other defaults
        $generation->setStartDateTime(new \DateTime());
        $generation->setEndDateTime((new \DateTime())->add(new \DateInterval('P1Y')));

        //get last generation of that type & "smartly" transfer properties
        $lastGenerations = $this->getDoctrine()->getRepository(EventGeneration::class)->findBy(['applied' => true], ['lastChangedAt' => 'DESC']);
        $lastGeneration = null;
        foreach ($lastGenerations as $lastGenerationLoop) {
            if ($lastGenerationLoop->getAssignEventTags()->contains($tag)) {
                $lastGeneration = $lastGenerationLoop;
                break;
            }
        }
        if (null !== $lastGeneration) {
            $generation->setStartDateTime(clone $lastGeneration->getEndDateTime());
            $generation->setEndDateTime(clone ($lastGeneration->getEndDateTime())->add($lastGeneration->getStartDateTime()->diff($lastGeneration->getEndDateTime())));
            $generation->setStartCronExpression($lastGeneration->getStartCronExpression());
            $generation->setEndCronExpression($lastGeneration->getEndCronExpression());

            $generation->setWeekdayWeight($lastGeneration->getWeekdayWeight());
            $generation->setSaturdayWeight($lastGeneration->getSaturdayWeight());
            $generation->setSundayWeight($lastGeneration->getSundayWeight());
            $generation->setHolidayWeight($lastGeneration->getHolidayWeight());
        }

        // save & start edit
        $this->fastSave($generation);

        return $this->redirectToRoute('administration_event_generation', ['generation' => $generation->getId()]);
    }

    /**
     * @Route("/generation/{generation}", name="administration_event_generation")
     *
     * @param EventGeneration $generation
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
     * @param EventGeneration $generation
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
     * @param Request         $request
     * @param EventGeneration $generation
     *
     * @return Response
     */
    public function generationUpdateAction(Request $request, EventGeneration $generation)
    {
        $content = json_decode($request->getContent(), true);
        $manager = $this->getDoctrine()->getManager();

        //simple props first
        $allowedProps = [
            'name' => 1, 'startCronExpression' => 1, 'endCronExpression' => 1,
            'startDateTime' => 2, 'endDateTime' => 2,
            'differentiateByEventType' => 3, 'mindPreviousEvents' => 3,
            'weekdayWeight' => 4, 'saturdayWeight' => 4, 'sundayWeight' => 4, 'holydayWeight' => 4,
            'step' => 5,
        ];
        foreach ($allowedProps as $prop => $valueType) {
            if (array_key_exists($prop, $content)) {
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
        if (array_key_exists('conflictEventTagIds', $content)) {
            $eventTagIds = $content['conflictEventTagIds'];
            $eventTags = $this->getDoctrine()->getRepository(EventTag::class)->findBy(['id' => $eventTagIds]);
            $generation->getConflictEventTags()->clear();
            foreach ($eventTags as $eventTag) {
                $generation->getConflictEventTags()->add($eventTag);
            }
        }
        if (array_key_exists('assignEventTagIds', $content)) {
            $eventTagIds = $content['assignEventTagIds'];
            $eventTags = $this->getDoctrine()->getRepository(EventTag::class)->findBy(['id' => $eventTagIds]);
            $generation->getAssignEventTags()->clear();
            foreach ($eventTags as $eventTag) {
                $generation->getAssignEventTags()->add($eventTag);
            }
        }
        if (array_key_exists('dateExceptions', $content)) {
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
        if (array_key_exists('targetClinics', $content)) {
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
        if (array_key_exists('targetDoctors', $content)) {
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

        $manager->persist($generation);
        $manager->flush();

        return $this->returnGeneration($generation);
    }

    /**
     * @Route("/generation/{generation}/events", name="administration_event_generation_events")
     *
     * @param EventGeneration                 $generation
     * @param EventGenerationServiceInterface $eventGenerationService
     *
     * @return Response
     */
    public function generateEvents(EventGeneration $generation, EventGenerationServiceInterface $eventGenerationService)
    {
        return $this->returnEvents($eventGenerationService->generate($generation));
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
     * @param Request                         $request
     * @param EventGeneration                 $generation
     * @param EventGenerationServiceInterface $eventGenerationService
     *
     * @return Response
     */
    public function generateApply(Request $request, EventGeneration $generation, EventGenerationServiceInterface $eventGenerationService)
    {
        //build up lookups
        $doctors = $this->getDoctrine()->getRepository(Doctor::class)->findBy(['deletedAt' => null]);
        $doctorLookup = [];
        foreach ($doctors as $doctor) {
            $doctorLookup[$doctor->getId()] = $doctor;
        }
        $clinics = $this->getDoctrine()->getRepository(Clinic::class)->findBy(['deletedAt' => null]);
        $clinicLookup = [];
        foreach ($clinics as $clinic) {
            $clinicLookup[$clinic->getId()] = $clinic;
        }

        //process submitted events
        $rawEvents = json_decode($request->getContent(), true);
        $events = [];
        foreach ($rawEvents as $rawEvent) {
            $event = new Event();
            $event->setStartDateTime(new \DateTime($rawEvent['startDateTime']));
            $event->setEndDateTime(new \DateTime($rawEvent['endDateTime']));
            $event->setEventType((int) $rawEvent['eventType']);

            $clinicId = $rawEvent['clinicId'];
            $event->setDoctor(array_key_exists($clinicId, $clinicLookup) ? $clinicLookup[$clinicId] : null);

            $doctorId = $rawEvent['doctorId'];
            $event->setDoctor(array_key_exists($doctorId, $doctorLookup) ? $doctorLookup[$doctorId] : null);
            $event->setGeneratedBy($generation);
            $events[] = $event;
        }

        $eventGenerationService->persist($generation, $events, $this->getUser());

        return $this->returnOk();
    }

    /**
     * @Route("/{event}/toggle_confirm", name="administration_event_toggle_confirm")
     *
     * @param Event $event
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
