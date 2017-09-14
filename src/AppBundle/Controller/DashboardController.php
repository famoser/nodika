<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Controller\Base\BaseFrontendController;
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
        if ($member != null) {
            $arr["eventLineModels"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($member->getOrganisation(), new \DateTime());
            $arr["organisation"] = $member->getOrganisation();
            $arr["member"] = $member;
        }

        $arr["leading_organisations"] = $this->getPerson()->getLeaderOf();
        $arr["my_organisations"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findByPerson($this->getPerson());
        return $this->render("dashboard/index.html.twig", $arr);
    }

    /**
     * @Route("/mine", name="dashboard_mine")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mineAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            $this->redirectToRoute("dashboard_index");
        }
        $arr["eventLineModels"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($member->getOrganisation(), new \DateTime(), $member);
        $arr["member"] = $member;
        $arr["organisation"] = $member->getOrganisation();
        return $this->render("dashboard/mine.html.twig", $arr);
    }
}