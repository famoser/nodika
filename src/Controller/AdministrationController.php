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

use App\Controller\Base\BaseController;
use App\Controller\Base\BaseFormController;
use App\Controller\Traits\EventControllerTrait;
use App\Entity\EventLine;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Form\Model\Event\AdvancedSearchType;
use App\Form\Model\Event\SearchType;
use App\Model\Event\SearchModel;
use App\Security\Voter\OrganisationVoter;
use App\Service\Interfaces\CsvServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
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

        $eventLineRepository = $this->getDoctrine()->getRepository(EventLine::class);
        $eventLineModels = $eventLineRepository->findEventLineModels($searchModel);

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

        $eventLineRepo = $this->getDoctrine()->getRepository(EventLine::class);
        $eventLineModels = $eventLineRepo->findEventLineModels($searchModel);

        if ($export) {
            return $csvService->renderCsv("export.csv", $this->toDataTable($eventLineModels, $translator), $this->getEventsHeader($translator));
        }

        $arr["event_line_models"] = $eventLineModels;
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

        $arr["frontend_users"] = $frontendUserRepo->findBy(["isEnabled" => true]);

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

        $arr["members"] = $memberRepo->findBy(["isEnabled" => true]);

        return $this->render('administration/members.html.twig', $arr);
    }

    /**
     * @Route("/event_lines", name="administration_event_lines")
     *
     * @return Response
     */
    public function eventLinesAction()
    {
        $eventLineRepo = $this->getDoctrine()->getRepository(EventLine::class);

        $arr["event_lines"] = $eventLineRepo->findAll();

        return $this->render('administration/event_lines.html.twig', $arr);
    }
}
