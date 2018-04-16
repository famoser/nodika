<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration;

use App\Controller\Base\BaseFormController;
use App\Entity\Member;
use App\Form\Member\RemoveMemberType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/members")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseFormController
{
    /**
     * @Route("/new", name="administration_member_new")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $myForm = $this->handleCreateForm($request, new Member());

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['new_form'] = $myForm->createView();

        return $this->render('administration/member/new.html.twig');
    }

    /**
     * @Route("/{member}/edit", name="administration_member_edit")
     *
     * @param Request $request
     * @param Member $member
     *
     * @return Response
     */
    public function editAction(Request $request, Member $member)
    {
        $myForm = $this->handleUpdateForm($request, $member);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['edit_form'] = $myForm->createView();

        return $this->render('administration/member/edit.html.twig');
    }

    /**
     * @Route("/{member}/remove", name="administration_member_remove")
     *
     * @param Request $request
     * @param Member $member
     *
     * @return Response
     */
    public function removeAction(Request $request, Member $member)
    {
        $canDelete = $member->getEvents()->count() == 0;
        $myForm = $this->handleForm(
            $this->createForm(RemoveMemberType::class, $member),
            $request,
            function () use ($member, $canDelete) {
                if ($canDelete) {
                    $this->fastRemove($member);
                } else {
                    $member->delete();
                    $this->fastSave($member);
                }
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["can_delete"] = $canDelete;
        $arr['remove_form'] = $myForm->createView();

        return $this->render('administration/member/remove.html.twig');
    }
}
