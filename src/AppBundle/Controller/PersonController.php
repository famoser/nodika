<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;

use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Member;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\FrontendUser\FrontendUserChangeEmailType;
use AppBundle\Form\FrontendUser\FrontendUserSetPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/person")
 * @Security("has_role('ROLE_USER')")
 */
class PersonController extends BaseFrontendController
{
    /**
     * @Route("/", name="person_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $arr["person"] = $this->getPerson();
        return $this->renderWithBackUrl("person/index.html.twig", $arr, $this->generateUrl("dashboard_index"));
    }

    /**
     * @Route("/change_personal", name="person_change_personal")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changePersonalAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $person = $this->getPerson();
        $myForm = $this->handleCrudForm(
            $request,
            $person,
            SubmitButtonType::EDIT,
            function ($form, $entity) {
                return $this->redirectToRoute("person_view");
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["member"] = $member;
        $arr["person"] = $person;
        $arr["edit_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'person/edit.html.twig', $arr, $this->generateUrl("person_view")
        );
    }

    /**
     * @Route("/change_email", name="person_change_email")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeEmailAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $user = $this->getPerson()->getFrontendUser();
        $myForm = $this->handleForm(
            $this->createForm(FrontendUserChangeEmailType::class),
            $request,
            clone($user),
            function ($form, $entity) use ($user) {
                /* @var \AppBundle\Entity\FrontendUser $entity */
                $exitingUser = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["email" => $entity->getEmail()]);
                $trans = $this->get("translator");
                if (!$exitingUser instanceof FrontendUser) {
                    //can change!
                    $user->setEmail($entity->getEmail());
                    $this->fastSave($user);
                    $this->displaySuccess($trans->trans("change_email.messages.changed_successfully", [], "person"));
                    return $this->redirectToRoute("person_view");
                } else {
                    $this->displayError($trans->trans("change_email.messages.email_already_picked", [], "person"));
                    return $form;
                }
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["change_email_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'person/change_email.html.twig', $arr, $this->generateUrl("person_view")
        );
    }

    /**
     * @Route("/change_password", name="person_change_password")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changePasswordAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $user = $this->getPerson()->getFrontendUser();
        $myForm = $this->handleForm(
            $this->createForm(FrontendUserSetPasswordType::class),
            $request,
            $user,
            function ($form, $entity) {
                /* @var \AppBundle\Entity\FrontendUser $entity */
                /* @var FrontendUser $user */
                if ($entity->isValidPlainPassword()) {
                    if ($entity->getPlainPassword() == $entity->getRepeatPlainPassword()) {
                        $entity->persistNewPassword();
                        $this->fastSave($entity);

                        $this->displayError($this->get("translator")->trans("success.password_set", [], "access"));
                        return $this->redirectToRoute("person_view");
                    } else {
                        $this->displayError($this->get("translator")->trans("error.passwords_do_not_match", [], "access"));
                    }
                } else {
                    $this->displayError($this->get("translator")->trans("error.new_password_not_valid", [], "access"));
                }
                return $form;
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["change_password_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'person/change_password.html.twig', $arr, $this->generateUrl("person_view")
        );
    }

    /**
     * @Route("/remove_member/{member}", name="person_remove_member")
     * @param Request $request
     * @param Member $member
     * @return Response
     */
    public function removeMemberAction(Request $request, Member $member)
    {
        $activeMember = $this->getMember();
        if ($activeMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        return $this->renderWithBackUrl(
            'person/remove_member.html.twig', ["member" => $member], $this->generateUrl("person_view")
        );
    }

    /**
     * @Route("/remove_member/{member}/confirm", name="person_remove_member_confirm")
     * @param Request $request
     * @param Member $member
     * @return Response
     */
    public function removeMemberConfirmAction(Request $request, Member $member)
    {
        $activeMember = $this->getMember();
        if ($activeMember == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $myPerson = $this->getPerson();
        $found = null;
        foreach ($member->getPersons() as $person) {
            if ($person->getId() == $myPerson->getId()) {
                $found = $person;
            }
        }
        $trans = $this->get("translator");
        if ($found != null) {
            $member->removePerson($found);
            $this->fastSave($member, $found);
            $this->displaySuccess($trans->trans("remove_member.messages.remove_successfully", [], "person"));
            return $this->redirectToRoute("access_logout");
        } else {
            $this->displayError($trans->trans("remove_member.messages.not_part_of_member", [], "person"));
            return $this->redirectToRoute("person_view");
        }
    }
}