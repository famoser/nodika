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
use App\Form\Model\Event\PublicSearchType;
use App\Model\Breadcrumb;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\CsvServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @return Response
     */
    public function eventsAction(Request $request, CsvServiceInterface $csvService, TranslatorInterface $translator)
    {
        $searchModel = new SearchModel(SearchModel::YEAR);

        $export = false;
        $form = $this->handleForm(
            $this->createForm(PublicSearchType::class, $searchModel)
                ->add('search', SubmitType::class, ['label' => 'form.filter'])
                ->add('export', SubmitType::class, ['label' => 'form.export']),
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
            return $csvService->renderCsv('export.csv', $this->toDataTable($events), $this->getEventsHeader($translator));
        }

        $arr['events'] = $events;
        $arr['form'] = $form->createView();

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

        /* @var Doctor[] $allDoctors */
        $allDoctors = $doctorRepo->findBy(['deletedAt' => null], ['familyName' => 'ASC', 'givenName' => 'ASC']);
        $arr['doctors'] = $allDoctors;

        $invitableDoctors = $doctorRepo->findBy(['deletedAt' => null, 'lastLoginDate' => null, 'isEnabled' => true], ['familyName' => 'ASC', 'givenName' => 'ASC']);
        $arr['invitable_doctors'] = $invitableDoctors;

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

        $arr['clinics'] = $clinicRepo->findBy(['deletedAt' => null], ['name' => 'ASC']);

        return $this->render('administration/clinics.html.twig', $arr);
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
