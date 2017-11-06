<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Model\Event\SearchEventModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * @Security("has_role('ROLE_USER')")
 */
class DashboardController extends BaseFrontendController
{
    /**
     * @Route("/", name="dashboard_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $member = $this->getMember();
        $arr["person"] = $this->getPerson();
        $arr["leading_organisations"] = $this->getPerson()->getLeaderOf();
        $all = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findByPerson($this->getPerson());

        if ($member != null) {
            $searchModel = new SearchEventModel($member->getOrganisation(), new \DateTime());
            $organisationRepo = $this->getDoctrine()->getRepository("AppBundle:Organisation");

            $arr["eventLineModels"] = $organisationRepo->findEventLineModels($searchModel);
            $arr["organisation"] = $member->getOrganisation();
            $arr["member"] = $member;
            unset($all[array_search($member->getOrganisation(), $all)]);
        }

        if (count($all) > 0) {
            $arr["change_organisations"] = $all;
        }
        return $this->renderNoBackUrl("dashboard/index.html.twig", $arr, "dashboard!");
    }

}