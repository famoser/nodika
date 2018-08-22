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
use App\Entity\Doctor;
use App\Form\Doctor\RemoveType;
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
class DoctorController extends BaseFormController
{
    /**
     * checks if the email is already used, and shows an error to the user if so
     *
     * @param Doctor $user
     * @param TranslatorInterface $translator
     * @return bool
     */
    private function emailNotUsed(Doctor $user, TranslatorInterface $translator)
    {
        $existing = $this->getDoctrine()->getRepository(Doctor::class)->findBy(["email" => $user->getEmail()]);
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
        $user = new Doctor();
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
     * @Route("/{doctor}/edit", name="administration_frontend_user_edit")
     *
     * @param Request $request
     * @param Doctor $doctor
     *
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function editAction(Request $request, Doctor $doctor, TranslatorInterface $translator)
    {
        $myForm = $this->handleUpdateForm(
            $request,
            $doctor,
            function () use ($doctor, $translator) {
                return $this->emailNotUsed($doctor, $translator);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/frontend_user/edit.html.twig', $arr);
    }

    /**
     * disable this route, as removing is not safe
     * @*Route("/{doctor}/remove", name="administration_frontend_user_remove")
     *
     * @param Request $request
     * @param Doctor $doctor
     *
     * @return Response
     */
    public function removeAction(Request $request, Doctor $doctor)
    {
        $canDelete = $doctor->getEvents()->count() == 0;
        $myForm = $this->handleForm(
            $this->createForm(RemoveType::class, $doctor),
            $request,
            function () use ($doctor, $canDelete) {
                if ($canDelete) {
                    $this->fastRemove($doctor);
                } else {
                    $doctor->delete();
                    $this->fastSave($doctor);
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
     * @Route("/{doctor}/toggle_login_enabled", name="administration_frontend_user_toggle_login_enabled")
     *
     * @param Doctor $doctor
     *
     * @return Response
     */
    public function toggleLoginEnabled(Doctor $doctor)
    {
        $doctor->setIsEnabled(!$doctor->isEnabled());
        $this->fastSave($doctor);
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
