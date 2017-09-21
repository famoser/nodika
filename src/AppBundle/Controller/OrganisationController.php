<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\Organisation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisation")
 * @Security("has_role('ROLE_USER')")
 */
class OrganisationController extends BaseFrontendController
{
    /**
     * @Route("/", name="organisation_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $arr["organisation"] = $member->getOrganisation();
        return $this->render("organisation/index.html.twig", $arr);
    }

    /**
     * @Route("/change_to/{organisation}", name="organisation_change_to")
     * @param Request $request
     * @param Organisation $organisation
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeToAction(Request $request, Organisation $organisation)
    {
        //check if part of organisation
        $person = $this->getPerson();
        foreach ($person->getMembers() as $member) {
            if ($member->getOrganisation()->getId() == $organisation->getId()) {
                $this->setMember($member);
            }
        }

        return $this->redirectToRoute("dashboard_index");
    }
}