<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Administration\Organisation\MemberController;
use AppBundle\Controller\Base\BaseController;
use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventOffer;
use AppBundle\Entity\EventOfferEntry;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Person;
use AppBundle\Enum\EventChangeType;
use AppBundle\Enum\OfferStatus;
use AppBundle\Enum\TradeTag;
use AppBundle\Helper\DateTimeConverter;
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
        return $this->render("dashboard/index.html.twig");
    }

    /**
     * @Route("/{organisation}/change_to", name="organisation_change_to")
     * @param Request $request
     * @param Organisation $organisation
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeToAction(Request $request, Organisation $organisation)
    {
        foreach ($this->getPerson()->getMembers() as $member) {
            if ($member->getOrganisation()->getId() == $organisation->getId()) {
                $this->setMember($member);
            }
        }
        return $this->redirectToRoute("dashboard_index");
    }
}