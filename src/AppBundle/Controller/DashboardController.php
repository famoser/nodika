<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


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

        $arr["person"] = $this->getPerson();
        $arr["leading_organisations"] = $this->getPerson()->getLeaderOf();
        $all = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findByPerson($this->getPerson());

        if ($member != null) {
            $arr["eventLineModels"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($member->getOrganisation(), new \DateTime());
            $arr["organisation"] = $member->getOrganisation();
            $arr["member"] = $member;
            unset($all[array_search($member->getOrganisation(), $all)]);
        }

        if (count($all) > 0) {
            $arr["change_organisations"] = $all;
        }
        return $this->renderNoBackUrl("dashboard/index.html.twig", $arr, "dashboard!");
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
            return $this->redirectToRoute("dashboard_index");
        }

        $arr["eventLineModels"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($member->getOrganisation(), new \DateTime(), null, $member, null, 4000);
        return $this->renderWithBackUrl("dashboard/mine.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }

    /**
     * @Route("/all", name="dashboard_all")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $arr["eventLineModels"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($member->getOrganisation(), new \DateTime(), null, null, null, 4000);
        $arr["member"] = $member;
        return $this->renderWithBackUrl("dashboard/index.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }

    /**
     * @Route("/search", name="dashboard_search")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $organisation = $member->getOrganisation();

        $startQuery = $request->query->get("start");
        $startDateTime = false;
        if ($startQuery != "") {
            $startDateTime = strtotime($startQuery);
        }
        if (!$startDateTime) {
            $startDateTime = new \DateTime();
        }

        $endQuery = $request->query->get("end");
        $endDateTime = false;
        if ($endQuery != "") {
            $endDateTime = strtotime($endQuery);
        }
        if (!$endDateTime) {
            $endDateTime = clone($startDateTime)->add(new \DateInterval("PT30D"));
        }

        $memberQuery = $request->query->get("membery");
        $member = null;
        if (is_numeric($memberQuery)) {
            foreach ($organisation->getMembers() as $organisationMember) {
                if ($organisationMember->getId() == $memberQuery) {
                    $member = $organisationMember;
                }
            }
        }


        $arr["eventLineModels"] = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($organisation, $startDateTime, $endDateTime, $member, null, 4000);
        $arr["member"] = $member;
        return $this->renderWithBackUrl("dashboard/index.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }
}