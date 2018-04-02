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

use App\Controller\Base\BaseController;
use App\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/member")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseController
{
    /**
     * @Route("/", name="member_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $memberRepo = $this->getDoctrine()->getRepository(Member::class);
        $arr['members'] = $memberRepo->findBy(["deletedAt" => null]);

        return $this->render('organisation/index.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }
}
