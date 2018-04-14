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
use App\Entity\EventLine;
use App\Entity\EventPast;
use App\Enum\EventChangeType;
use App\Form\Model\Event\SearchType;
use App\Helper\DateTimeFormatter;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\CsvServiceInterface;
use App\Service\Interfaces\SettingServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/search")
 * @Security("has_role('ROLE_USER')")
 */
class SearchController extends BaseFormController
{
    use EventControllerTrait;

    /**
     * @Route("/", name="search_index")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function searchAction(Request $request, TranslatorInterface $translator, CsvServiceInterface $csvService)
    {
        $searchModel = new SearchModel();

        $export = false;
        $form = $this->handleForm(
            $this->createForm(SearchType::class, $searchModel)
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

        return $this->render('event/search.html.twig', $arr);
    }
}
