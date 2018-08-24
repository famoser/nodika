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

use App\Controller\Base\BaseFormController;
use App\Controller\Traits\EventControllerTrait;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\EventGeneration;
use App\Form\Model\Event\AdvancedSearchType;
use App\Model\Breadcrumb;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\CsvServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/administration")
 */
class AdministrationController extends BaseFormController
{
    use EventControllerTrait;

    /**
     * @Route("/", name="administration_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $searchModel = new SearchModel(SearchModel::MONTH);
        $searchModel->setIsConfirmed(false);

        $eventRepository = $this->getDoctrine()->getRepository(Event::class);
        $eventLineModels = $eventRepository->search($searchModel);

        $arr['unconfirmed_events'] = $eventLineModels;

        return $this->render('administration/index.html.twig', $arr);
    }

    /**
     * @Route("/events", name="administration_events")
     *
     * @param Request             $request
     * @param CsvServiceInterface $csvService
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function eventsAction(Request $request, CsvServiceInterface $csvService, TranslatorInterface $translator)
    {
        $searchModel = new SearchModel(SearchModel::MONTH);

        $export = false;
        $form = $this->handleForm(
            $this->createForm(AdvancedSearchType::class, $searchModel)
                ->add('search', SubmitType::class)
                ->add('export', SubmitType::class),
            $request,
            function ($form) use (&$export) {
                /* @var Form $form */
                $export = $form->get('export')->isClicked();

                return $form;
            }
        );

        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepo->search($searchModel);

        if ($export) {
            return $csvService->renderCsv('export.csv', $this->toDataTable($events, $translator), $this->getEventsHeader($translator));
        }

        $arr['events'] = $events;
        $arr['search_form'] = $form;

        return $this->render('administration/events.html.twig', $arr);
    }

    /**
     * @Route("/doctors", name="administration_doctors")
     *
     * @return Response
     */
    public function doctorsAction()
    {
        $doctorRepo = $this->getDoctrine()->getRepository(Doctor::class);

        /* @var Doctor[] $doctors */
        $doctors = $doctorRepo->findBy(['deletedAt' => null]);

        $arr['doctors'] = $doctors;

        return $this->render('administration/doctors.html.twig', $arr);
    }

    /**
     * @Route("/clinics", name="administration_clinics")
     *
     * @return Response
     */
    public function clinicsAction()
    {
        $clinicRepo = $this->getDoctrine()->getRepository(Clinic::class);

        $arr['clinics'] = $clinicRepo->findBy(['deletedAt' => null]);

        return $this->render('administration/clinics.html.twig', $arr);
    }

    /**
     * @Route("/generations", name="administration_generations")
     *
     * @return Response
     */
    public function generationsAction()
    {
        $eventGenerations = $this->getDoctrine()->getRepository(EventGeneration::class)->findAll();

        $arr['event_generations'] = $eventGenerations;

        return $this->render('administration/settings.html.twig', $arr);
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl('administration_index'),
                $this->getTranslator()->trans('index.title', [], 'administration')
            ),
        ];
    }
}
