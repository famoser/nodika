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

use App\Controller\Base\BaseDoctrineController;
use App\Entity\EventLine;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\SettingServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 * @Security("has_role('ROLE_USER')")
 */
class IndexController extends BaseDoctrineController
{
    /**
     * @Route("/", name="index_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $searchModel = new SearchModel();
        $eventLineRepository = $this->getDoctrine()->getRepository(EventLine::class);

        $eventLineModels = $eventLineRepository->findEventLineModels($searchModel);
        $arr['event_line_models'] = $eventLineModels;

        return $this->render('dashboard/index.html.twig', $arr);
    }

    /**
     * @Route("/confirm", name="index_confirm")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAction(SettingServiceInterface $settingService)
    {
        $searchModel = new SearchModel();
        $searchModel->setIsConfirmed(false);
        $searchModel->setFrontendUser($this->getUser());
        $end = new \DateTime();
        $end->add($settingService->getCanConfirmEventAt());
        $searchModel->setStartDateTime(new \DateTime());
        $searchModel->setEndDateTime($end);

        $eventLineModels = $this->getDoctrine()->getRepository('App:EventLine')->findEventLineModels($searchModel);
        $arr['event_line_models'] = $eventLineModels;

        return $this->render('event/confirm.html.twig', $arr);
    }
}
