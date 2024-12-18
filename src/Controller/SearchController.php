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
use App\Form\Model\Event\PublicSearchType;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\CsvServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/search")
 */
class SearchController extends BaseFormController
{
    use EventControllerTrait;

    /**
     * @Route("/", name="search_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, TranslatorInterface $translator, CsvServiceInterface $csvService)
    {
        $searchModel = new SearchModel(SearchModel::MONTH);

        $export = false;
        $form = $this->handleForm(
            $this->createForm(PublicSearchType::class, $searchModel)
                ->add('filter', SubmitType::class, ['label' => 'form.filter'])
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
            return $csvService->renderCsv('export.csv', $this->toSummaryTable($events));
        }

        $arr['events'] = $events;
        $arr['form'] = $form->createView();

        return $this->render('search/index.html.twig', $arr);
    }
}
