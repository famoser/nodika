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
use App\Entity\FrontendUser;
use App\Form\FrontendUser\RemoveFrontendUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/frontend_users")
 * @Security("has_role('ROLE_USER')")
 */
class FrontendUserController extends BaseFormController
{
    /**
     * @Route("/new", name="administration_frontend_user_new")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $myForm = $this->handleCreateForm($request, new FrontendUser());

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['new_form'] = $myForm->createView();

        return $this->render('administration/frontend_user/new.html.twig');
    }

    /**
     * @Route("/{frontendUser}/edit", name="administration_frontend_user_edit")
     *
     * @param Request $request
     * @param FrontendUser $frontendUser
     *
     * @return Response
     */
    public function editAction(Request $request, FrontendUser $frontendUser)
    {
        $myForm = $this->handleUpdateForm($request, $frontendUser);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['edit_form'] = $myForm->createView();

        return $this->render('administration/frontend_user/edit.html.twig');
    }

    /**
     * @Route("/{frontendUser}/remove", name="administration_frontend_user_remove")
     *
     * @param Request $request
     * @param FrontendUser $frontendUser
     *
     * @return Response
     */
    public function removeAction(Request $request, FrontendUser $frontendUser)
    {
        $canDelete = $frontendUser->getEvents()->count() == 0;
        $myForm = $this->handleForm(
            $this->createForm(RemoveFrontendUserType::class, $frontendUser),
            $request,
            function () use ($frontendUser, $canDelete) {
                if ($canDelete) {
                    $this->fastRemove($frontendUser);
                } else {
                    $frontendUser->delete();
                    $this->fastSave($frontendUser);
                }
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["can_delete"] = $canDelete;
        $arr['remove_form'] = $myForm->createView();

        return $this->render('administration/frontend_user/remove.html.twig');
    }
}
