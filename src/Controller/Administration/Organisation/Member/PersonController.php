<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 15:27
 */

namespace App\Controller\Administration\Organisation\Member;

use App\Controller\Base\BaseController;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Enum\SubmitButtonType;
use App\Security\Voter\MemberVoter;
use App\Security\Voter\PersonVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/persons")
 * @Security("has_role('ROLE_USER')")
 */
class PersonController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_member_person_new")
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation, Member $member)
    {
        $this->denyAccessUnlessGranted(MemberVoter::EDIT, $organisation);

        $person = new Person();
        $person->addMember($member);
        $myForm = $this->handleCrudForm(
            $request,
            $person,
            SubmitButtonType::CREATE,
            function ($form, $entity) use ($organisation, $member) {
                /* @var Form $form */
                /* @var Member $entity */
                //return $this->redirectToRoute("administration_organisation_member_administer", ["organisation" => $organisation->getId(), "member" => $entity->getId()]);
                return $this->redirectToRoute("administration_organisation_member_person_new", ["organisation" => $organisation->getId(), "member" => $member->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["organisation"] = $organisation;
        $arr["member"] = $member;
        $arr["new_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'administration/organisation/member/person/new.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_member_administer", ["organisation" => $organisation->getId(), "member" => $member->getId()])
        );
    }

    /**
     * @Route("/{person}/edit", name="administration_organisation_member_person_edit")
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @param Person $person
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, Member $member, Person $person)
    {
        $this->denyAccessUnlessGranted(PersonVoter::EDIT, $member);

        $myForm = $this->handleCrudForm(
            $request,
            $person,
            SubmitButtonType::EDIT,
            function ($form, $entity) use ($organisation, $member) {
                /* @var Member $entity */
                /* @var Form $form */
                return $this->redirectToRoute("administration_organisation_member_administer", ["organisation" => $organisation->getId(), "member" => $member->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["organisation"] = $organisation;
        $arr["member"] = $member;
        $arr["person"] = $person;
        $arr["edit_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'administration/organisation/member/person/edit.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_member_administer", ["organisation" => $organisation->getId(), "member" => $member->getId()])
        );
    }

    /**
     * @Route("/{person}/remove", name="administration_organisation_member_person_remove")
     * @param Request $request
     * @param Organisation $organisation
     * @param Member $member
     * @param Person $person
     * @return Response
     */
    public function removeAction(Request $request, Organisation $organisation, Member $member, Person $person)
    {
        $this->denyAccessUnlessGranted(PersonVoter::REMOVE, $member);

        $myForm = $this->handleCrudForm(
            $request,
            $person,
            SubmitButtonType::REMOVE,
            function ($form, $entity) use ($organisation, $member) {
                /* @var Member $entity */
                /* @var Form $form */
                return $this->redirectToRoute("administration_organisation_member_administer", ["organisation" => $organisation->getId(), "member" => $member->getId()]);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["organisation"] = $organisation;
        $arr["member"] = $member;
        $arr["person"] = $person;
        $arr["remove_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'administration/organisation/member/person/remove.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_member_person_edit", ["organisation" => $organisation->getId(), "member" => $member->getId(), "person" => $person->getId()])
        );
    }
}
