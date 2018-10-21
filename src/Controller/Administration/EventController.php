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

use App\Controller\Administration\Base\BaseController;
use App\Entity\Event;
use App\Entity\EventGeneration;
use App\Entity\EventPast;
use App\Entity\EventTag;
use App\Enum\EventChangeType;
use App\Enum\EventTagType;
use App\Form\Event\RemoveType;
use App\Model\Breadcrumb;
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
class EventController extends BaseController
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
            dump($tagType);

            return $this->redirectToRoute('administration_event_generations');
        }

        $generation = new EventGeneration();
        $generation->getAssignEventTags()->add($tag);
        $generation->registerChangeBy($this->getUser());

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

        //set conflict tags as all
        $tags = $this->getDoctrine()->getRepository(EventTag::class)->findAll();
        foreach ($tags as $tag) {
            $generation->getConflictEventTags()->add($tag);
        }

        //get last generation of that type & "smartly" transfer properties
        $lastGenerations = $this->getDoctrine()->getRepository(EventGeneration::class)->findBy([], ['lastChangedAt' => 'DESC']);
        $lastGeneration = null;
        foreach ($lastGenerations as $lastGenerationLoop) {
            if ($lastGenerationLoop->getAssignEventTags()->contains($tag)) {
                $lastGeneration = $lastGenerationLoop;
                break;
            }
        }
        if (null !== $lastGeneration) {
            $generation->setStartDateTime($lastGeneration->getEndDateTime());
            $generation->setEndDateTime($lastGeneration->getStartDateTime()->add($lastGeneration->getEndDateTime()->diff($lastGeneration->getStartDateTime())));
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
