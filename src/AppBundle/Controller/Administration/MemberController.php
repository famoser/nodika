<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 15:27
 */

namespace AppBundle\Controller\Administration;


use AppBundle\Controller\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/organisation/{organisation}/member")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_member_new")
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        return $this->render(
            ':administration/organisation/member/add.html.twig', []
        );
    }

    /**
     * @Route("/import", name="administration_organisation_member_import")
     * @param Request $request
     * @return Response
     */
    public function importAction(Request $request)
    {
        return $this->render(
            ':administration/organisation/member/add.html.twig', []
        );
    }
}