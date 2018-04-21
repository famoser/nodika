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
use App\Entity\Event;
use App\Entity\EventGeneration;
use App\Entity\EventTag;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Entity\Settings;
use App\Form\Model\Event\AdvancedSearchType;
use App\Model\Breadcrumb;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\CsvServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/administration")
 * @Security("has_role('ROLE_USER')")
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
        $searchModel = new SearchModel();
        $searchModel->setIsConfirmed(false);

        $eventRepository = $this->getDoctrine()->getRepository(Event::class);
        $eventLineModels = $eventRepository->search($searchModel);

        $arr['unconfirmed_events'] = $eventLineModels;

        return $this->render('administration/index.html.twig', $arr);
    }

    /**
     * @Route("/events", name="administration_events")
     *
     * @param Request $request
     * @param CsvServiceInterface $csvService
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function eventsAction(Request $request, CsvServiceInterface $csvService, TranslatorInterface $translator)
    {
        $searchModel = new SearchModel();

        $export = false;
        $form = $this->handleForm(
            $this->createForm(AdvancedSearchType::class, $searchModel)
                ->add("search", SubmitType::class)
                ->add("export", SubmitType::class),
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
            return $csvService->renderCsv("export.csv", $this->toDataTable($events, $translator), $this->getEventsHeader($translator));
        }

        $arr["events"] = $events;
        $arr["search_form"] = $form;

        return $this->render('administration/events.html.twig', $arr);
    }

    /**
     * @Route("/frontend_users", name="administration_frontend_users")
     *
     * @return Response
     */
    public function frontendUsersAction()
    {
        $frontendUserRepo = $this->getDoctrine()->getRepository(FrontendUser::class);

        /* @var FrontendUser[] $frontendUsers */
        $frontendUsers = $frontendUserRepo->findBy(["deletedAt" => null]);

        $arr["frontend_users"] = $frontendUsers;
        return $this->render('administration/frontend_users.html.twig', $arr);
    }

    /**
     * @Route("/members", name="administration_members")
     *
     * @return Response
     */
    public function membersAction()
    {
        $memberRepo = $this->getDoctrine()->getRepository(Member::class);

        $arr["members"] = $memberRepo->findBy(["deletedAt" => null]);

        return $this->render('administration/members.html.twig', $arr);
    }

    /**
     * @Route("/settings", name="administration_settings")
     *
     * @return Response
     */
    public function settingsAction()
    {
        $settings = $this->getDoctrine()->getRepository(Settings::class)->findSingle();

        $arr["settings"] = $settings;

        return $this->render('administration/settings.html.twig', $arr);
    }

    /**
     * @Route("/generations", name="administration_generations")
     *
     * @return Response
     */
    public function generationsAction()
    {
        $eventGenerations = $this->getDoctrine()->getRepository(EventGeneration::class)->findAll();

        $arr["event_generations"] = $eventGenerations;

        return $this->render('administration/settings.html.twig', $arr);
    }

    /**
     * get the breadcrumbs leading to this controller
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl("administration_index"),
                $this->getTranslator()->trans("index.title", [], "administration")
            )
        ];
    }
}
