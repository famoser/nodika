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
use App\Entity\Organisation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisation")
 * @Security("has_role('ROLE_USER')")
 */
class OrganisationController extends BaseFrontendController
{
    /**
     * @Route("/", name="organisation_view")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $arr['organisation'] = $member->getOrganisation();

        return $this->renderWithBackUrl('organisation/index.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }

    /**
     * @Route("/change_to/{organisation}", name="organisation_change_to")
     *
     * @param Organisation $organisation
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeToAction(Organisation $organisation)
    {
        //check if part of organisation
        $person = $this->getPerson();
        foreach ($person->getMembers() as $member) {
            if ($member->getOrganisation()->getId() === $organisation->getId()) {
                $this->setMember($member);
            }
        }

        return $this->redirectToRoute('dashboard_index');
    }
}
