<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;

use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\Person;
use AppBundle\Enum\SubmitButtonType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/member")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseFrontendController
{
    /**
     * @Route("/", name="member_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $arr["member"] = $member;
        return $this->renderWithBackUrl("member/index.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }

    /**
     * @Route("/edit", name="member_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $myForm = $this->handleCrudForm(
            $request,
            $member,
            SubmitButtonType::EDIT,
            function ($form, $entity) {
                return $this->redirectToRoute("member_view");
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["member"] = $member;
        $arr["person"] = $this->getPerson();;
        $arr["edit_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'member/edit.html.twig', $arr, $this->generateUrl("member_view")
        );
    }

    /**
     * @Route("/remove_person/{person}", name="member_remove_person")
     * @param Request $request
     * @param Person $person
     * @return Response
     */
    public function removePersonAction(Request $request, Person $person)
    {
        $activeMember = $this->getMember();
        if ($activeMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $myPerson = $this->getPerson();
        return $this->renderWithBackUrl(
            'member/remove_person.html.twig', ["person" => $person, "my_person" => $myPerson], $this->generateUrl("member_view")
        );
    }

    /**
     * @Route("/remove_person/{person}/confirm", name="member_remove_person_confirm")
     * @param Request $request
     * @param Person $person
     * @return Response
     */
    public function removePersonConfirmAction(Request $request, Person $person)
    {
        $activeMember = $this->getMember();
        if ($activeMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $trans = $this->get("translator");
        $myPerson = $this->getPerson();
        if ($myPerson->getId() == $person->getId()) {
            //remove self!
            $activeMember->removePerson($myPerson);
            $this->fastSave($activeMember, $myPerson);
            $this->displaySuccess($trans->trans("remove_person.messages.remove_successfully", [], "member"));
            return $this->redirectToRoute("access_logout");
        }
        $found = null;
        foreach ($activeMember->getPersons() as $person) {
            if ($person->getId() == $myPerson->getId()) {
                $found = $person;
            }
        }
        if ($found != null) {
            $activeMember->removePerson($found);
            $this->fastSave($activeMember, $found);
            $this->displaySuccess($trans->trans("remove_person.messages.remove_successfully", [], "member"));
            return $this->redirectToRoute("access_logout");
        } else {
            $this->displayError($trans->trans("remove_person.messages.not_part_of_member", [], "member"));
            return $this->redirectToRoute("member_view");
        }
    }
}