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
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Enum\SubmitButtonType;
use App\Form\FrontendUser\FrontendUserChangeEmailType;
use App\Form\FrontendUser\FrontendUserSetPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/person")
 * @Security("has_role('ROLE_USER')")
 */
class PersonController extends BaseFrontendController
{
    /**
     * @Route("/", name="person_view")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $arr['person'] = $this->getPerson();

        return $this->renderWithBackUrl('person/index.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }

    /**
     * @Route("/change_personal", name="person_change_personal")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function changePersonalAction(Request $request, TranslatorInterface $translator)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $person = $this->getPerson();
        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $person,
            SubmitButtonType::EDIT,
            function ($form, $entity) {
                return $this->redirectToRoute('person_view');
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['member'] = $member;
        $arr['person'] = $person;
        $arr['edit_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'person/edit.html.twig',
            $arr,
            $this->generateUrl('person_view')
        );
    }

    /**
     * @Route("/change_email", name="person_change_email")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function changeEmailAction(Request $request, TranslatorInterface $translator)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $user = $this->getPerson()->getFrontendUser();
        $myForm = $this->handleForm(
            $this->createForm(FrontendUserChangeEmailType::class),
            $request,
            $translator,
            clone $user,
            function ($form, $entity) use ($user, $translator) {
                /* @var \App\Entity\FrontendUser $entity */
                $exitingUser = $this->getDoctrine()->getRepository('App:FrontendUser')->findOneBy(['email' => $entity->getEmail()]);
                if (!$exitingUser instanceof FrontendUser) {
                    //can change!
                    $user->setEmail($entity->getEmail());
                    $this->fastSave($user);
                    $this->displaySuccess($translator->trans('change_email.messages.changed_successfully', [], 'person'));

                    return $this->redirectToRoute('person_view');
                }
                $this->displayError($translator->trans('change_email.messages.email_already_picked', [], 'person'));

                return $form;
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['change_email_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'person/change_email.html.twig',
            $arr,
            $this->generateUrl('person_view')
        );
    }

    /**
     * @Route("/change_password", name="person_change_password")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function changePasswordAction(Request $request, TranslatorInterface $translator)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $user = $this->getPerson()->getFrontendUser();
        $myForm = $this->handleForm(
            $this->createForm(FrontendUserSetPasswordType::class),
            $request,
            $translator,
            $user,
            function ($form, $entity) use ($translator) {
                /* @var \App\Entity\FrontendUser $entity */
                /* @var FrontendUser $user */
                if ($entity->isValidPlainPassword()) {
                    if ($entity->getPlainPassword() === $entity->getRepeatPlainPassword()) {
                        $entity->persistNewPassword();
                        $this->fastSave($entity);

                        $this->displayError($translator->trans('success.password_set', [], 'access'));

                        return $this->redirectToRoute('person_view');
                    }
                    $this->displayError($translator->trans('error.passwords_do_not_match', [], 'access'));
                } else {
                    $this->displayError($translator->trans('error.new_password_not_valid', [], 'access'));
                }

                return $form;
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['change_password_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'person/change_password.html.twig',
            $arr,
            $this->generateUrl('person_view')
        );
    }

    /**
     * @Route("/remove_member/{member}", name="person_remove_member")
     *
     * @param Member $member
     *
     * @return Response
     */
    public function removeMemberAction(Member $member)
    {
        $activeMember = $this->getMember();
        if (null === $activeMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        return $this->renderWithBackUrl(
            'person/remove_member.html.twig',
            ['member' => $member],
            $this->generateUrl('person_view')
        );
    }

    /**
     * @Route("/remove_member/{member}/confirm", name="person_remove_member_confirm")
     *
     * @param Member $member
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function removeMemberConfirmAction(Member $member, TranslatorInterface $translator)
    {
        $activeMember = $this->getMember();
        if (null === $activeMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        $myPerson = $this->getPerson();
        $found = null;
        foreach ($member->getPersons() as $person) {
            if ($person->getId() === $myPerson->getId()) {
                $found = $person;
            }
        }
        if (null !== $found) {
            $member->removePerson($found);
            $this->fastSave($member, $found);
            $this->displaySuccess($translator->trans('remove_member.messages.remove_successfully', [], 'person'));

            return $this->redirectToRoute('access_logout');
        }
        $this->displayError($translator->trans('remove_member.messages.not_part_of_member', [], 'person'));

        return $this->redirectToRoute('person_view');
    }
}
