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
use App\Entity\Person;
use App\Enum\SubmitButtonType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/member")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseFrontendController
{
    /**
     * @Route("/", name="member_view")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $arr['member'] = $member;

        return $this->renderWithBackUrl('member/index.html.twig', $arr, $this->generateUrl('dashboard_index'));
    }

    /**
     * @Route("/edit", name="member_edit")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function editAction(Request $request, TranslatorInterface $translator)
    {
        $member = $this->getMember();
        if (null === $member) {
            return $this->redirectToRoute('dashboard_index');
        }

        $myForm = $this->handleCrudForm(
            $request,
            $translator,
            $member,
            SubmitButtonType::EDIT,
            function ($form, $entity) {
                return $this->redirectToRoute('member_view');
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['member'] = $member;
        $arr['person'] = $this->getPerson();

        $arr['edit_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'member/edit.html.twig',
            $arr,
            $this->generateUrl('member_view')
        );
    }

    /**
     * @Route("/remove_person/{person}", name="member_remove_person")
     *
     * @param Person $person
     *
     * @return Response
     */
    public function removePersonAction(Person $person)
    {
        $activeMember = $this->getMember();
        if (null === $activeMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        $myPerson = $this->getPerson();

        return $this->renderWithBackUrl(
            'member/remove_person.html.twig',
            ['person' => $person, 'my_person' => $myPerson],
            $this->generateUrl('member_view')
        );
    }

    /**
     * @Route("/remove_person/{person}/confirm", name="member_remove_person_confirm")
     *
     * @param Person $person
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function removePersonConfirmAction(Person $person, TranslatorInterface $translator)
    {
        $activeMember = $this->getMember();
        if (null === $activeMember) {
            return $this->redirectToRoute('dashboard_index');
        }

        $myPerson = $this->getPerson();
        if ($myPerson->getId() === $person->getId()) {
            //remove self!
            $activeMember->removePerson($myPerson);
            $this->fastSave($activeMember, $myPerson);
            $this->displaySuccess($translator->trans('remove_person.messages.remove_successfully', [], 'member'));

            return $this->redirectToRoute('access_logout');
        }
        $found = null;
        foreach ($activeMember->getPersons() as $person) {
            if ($person->getId() === $myPerson->getId()) {
                $found = $person;
            }
        }
        if (null !== $found) {
            $activeMember->removePerson($found);
            $this->fastSave($activeMember, $found);
            $this->displaySuccess($translator->trans('remove_person.messages.remove_successfully', [], 'member'));

            return $this->redirectToRoute('access_logout');
        }
        $this->displayError($translator->trans('remove_person.messages.not_part_of_member', [], 'member'));

        return $this->redirectToRoute('member_view');
    }
}
