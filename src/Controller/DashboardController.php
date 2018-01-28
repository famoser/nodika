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

use App\Controller\Base\BaseFrontendController;
use App\Model\Event\SearchEventModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * @Security("has_role('ROLE_USER')")
 */
class DashboardController extends BaseFrontendController
{
    /**
     * @Route("/", name="dashboard_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $member = $this->getMember();
        $arr['person'] = $this->getPerson();
        $arr['leading_organisations'] = $this->getPerson()->getLeaderOf();
        $all = $this->getDoctrine()->getRepository('App:Organisation')->findByPerson($this->getPerson());

        if (null !== $member) {
            $searchModel = new SearchEventModel($member->getOrganisation(), new \DateTime());
            $searchModel->setEndDateTime(new \DateTime('today + 1 month'));

            $organisationRepo = $this->getDoctrine()->getRepository('App:Organisation');

            $eventLineModels = $organisationRepo->findEventLineModels($searchModel);
            $arr['has_active_events'] = $organisationRepo->addActiveEvents($eventLineModels);
            $arr['event_line_models'] = $eventLineModels;
            $arr['organisation'] = $member->getOrganisation();
            $arr['member'] = $member;
            unset($all[array_search($member->getOrganisation(), $all, true)]);
        }

        $arr['change_organisations'] = $all;

        return $this->renderNoBackUrl('dashboard/index.html.twig', $arr, 'dashboard!');
    }

    /**
     * @Route("/about", name="dashboard_about")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function aboutAction()
    {
        $member = $this->getMember();
        $arr['person'] = $this->getPerson();
        if (null !== $member) {
            $arr['member'] = $member;
            $arr['organisation'] = $member->getOrganisation();
        }
        return $this->renderNoBackUrl('dashboard/about.html.twig', $arr, 'dashboard!');
    }
}
