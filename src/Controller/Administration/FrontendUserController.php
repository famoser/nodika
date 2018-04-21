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
use App\Model\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/frontend_users")
 * @Security("has_role('ROLE_USER')")
 */
class FrontendUserController extends BaseFormController
{
    /**
     * checks if the email is already used, and shows an error to the user if so
     *
     * @param FrontendUser $user
     * @param TranslatorInterface $translator
     * @return bool
     */
    private function emailNotUsed(FrontendUser $user, TranslatorInterface $translator)
    {
        $existing = $this->getDoctrine()->getRepository(FrontendUser::class)->findBy(["email" => $user->getEmail()]);
        if (count($existing) > 0) {
            $this->displayError($translator->trans("error.email_not_unique", [], "trait_user"));
            return false;
        }
        return true;
    }

    /**
     * @Route("/new", name="administration_frontend_user_new")
     *
     * @param Request $request
     *
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function newAction(Request $request, TranslatorInterface $translator)
    {
        $user = new FrontendUser();
        $user->setPlainPassword(uniqid());
        $user->setPassword();
        $user->setRegistrationDate(new \DateTime());

        $myForm = $this->handleCreateForm(
            $request,
            $user,
            function () use ($user, $translator) {
                return $this->emailNotUsed($user, $translator);
            }
        );
        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/frontend_user/new.html.twig', $arr);
    }

    /**
     * @Route("/{frontendUser}/edit", name="administration_frontend_user_edit")
     *
     * @param Request $request
     * @param FrontendUser $frontendUser
     *
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function editAction(Request $request, FrontendUser $frontendUser, TranslatorInterface $translator)
    {
        $myForm = $this->handleUpdateForm(
            $request,
            $frontendUser,
            function () use ($frontendUser, $translator) {
                return $this->emailNotUsed($frontendUser, $translator);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/frontend_user/edit.html.twig', $arr);
    }

    /**
     * disable this route, as it is not safe
     * @*Route("/{frontendUser}/remove", name="administration_frontend_user_remove")
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
        $arr['form'] = $myForm->createView();

        return $this->render('administration/frontend_user/remove.html.twig', $arr);
    }

    /**
     * @Route("/{frontendUser}/toggle_login_enabled", name="administration_frontend_user_toggle_login_enabled")
     *
     * @param FrontendUser $frontendUser
     *
     * @return Response
     */
    public function toggleLoginEnabled(FrontendUser $frontendUser)
    {
        $frontendUser->setIsEnabled(!$frontendUser->isEnabled());
        $this->fastSave($frontendUser);
        return $this->redirectToRoute("administration_frontend_users");
    }

    /**
     * get the breadcrumbs leading to this controller
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl("administration_index"),
                $this->getTranslator()->trans("index.title", [], "administration")
            ),
            new Breadcrumb(
                $this->generateUrl("administration_frontend_users"),
                $this->getTranslator()->trans("frontend_users.title", [], "administration")
            )
        ];
    }
}
