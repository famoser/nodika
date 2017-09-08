<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 14:22
 */

namespace AppBundle\Controller\Administration\Organisation\Member;


use AppBundle\Controller\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/person")
 * @Security("has_role('ROLE_USER')")
 */
class PersonController extends BaseController
{
    /**
     * @Route("/{person}/view", name="administration_organisation_person_view")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function viewAction(Request $request)
    {
        return $this->render(
            'administration/organisation/person/view.html.twig', []
        );
    }
}