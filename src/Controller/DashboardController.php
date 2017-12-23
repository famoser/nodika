<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace App\Controller;

use App\Controller\Base\BaseFrontendController;
use App\Model\Event\SearchEventModel;
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $member = $this->getMember();
        $arr["person"] = $this->getPerson();
        $arr["leading_organisations"] = $this->getPerson()->getLeaderOf();
        $all = $this->getDoctrine()->getRepository("App:Organisation")->findByPerson($this->getPerson());

        if ($member != null) {
            $searchModel = new SearchEventModel($member->getOrganisation(), new \DateTime());
            $searchModel->setEndDateTime(new \DateTime("today + 2 month"));

            $organisationRepo = $this->getDoctrine()->getRepository("App:Organisation");

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
