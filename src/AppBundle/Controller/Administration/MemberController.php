<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 15:27
 */

namespace AppBundle\Controller\Administration;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Form\Member\NewMemberType;
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
    public function newAction(Request $request, Organisation $organisation)
    {
        $newMemberForm = $this->createForm(NewMemberType::class);
        $arr = [];

        $organisation = new Member();
        $newMemberForm->setData($organisation);
        $newMemberForm->handleRequest($request);

        if ($newMemberForm->isSubmitted()) {
            if ($newMemberForm->isValid()) {
                $organisation->setOrganisation($organisation);
                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();

                //TODO: empty form
                $this->displaySuccess($this->get("translator")->trans("info.member_add_successful", [], "member"));
                $newMemberForm->setData(new Member());
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["new_member_form"] = $newMemberForm->createView();
        return $this->render(
            'administration/organisation/member/new.html.twig', $arr
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
            ':administration/organisation/member/import.html.twig', []
        );
    }
}