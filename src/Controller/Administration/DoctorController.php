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

use App\Controller\Administration\Base\BaseController;
use App\Entity\Doctor;
use App\Form\Doctor\RemoveType;
use App\Model\Breadcrumb;
use App\Service\InviteEmailService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/doctors")
 */
class DoctorController extends BaseController
{
    /**
     * checks if the email is already used, and shows an error to the user if so.
     *
     * @return bool
     */
    private function emailNotUsed(Doctor $user, TranslatorInterface $translator)
    {
        $existing = $this->getDoctrine()->getRepository(Doctor::class)->findBy(['email' => $user->getEmail()]);
        if (\count($existing) > 0) {
            $this->displayError($translator->trans('new.danger.email_not_unique', [], 'administration_doctor'));

            return false;
        }

        return true;
    }

    /**
     * @Route("/new", name="administration_doctor_new")
     *
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

        return $this->render('administration/doctor/new.html.twig', $arr);
    }

    /**
     * @Route("/{doctor}/edit", name="administration_doctor_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, Doctor $doctor, TranslatorInterface $translator)
    {
        $beforeEmail = $doctor->getEmail();
        $myForm = $this->handleUpdateForm(
            $request,
            $doctor,
            function () use ($doctor, $translator, $beforeEmail) {
                return $beforeEmail === $doctor->getEmail() || $this->emailNotUsed($doctor, $translator);
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/doctor/edit.html.twig', $arr);
    }

    /**
     * deactivated because not safe.
     *
     * @*Route("/{doctor}/remove", name="administration_doctor_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, Doctor $doctor)
    {
        $canDelete = 0 === $doctor->getEvents()->count();
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

        $arr['can_delete'] = $canDelete;
        $arr['form'] = $myForm->createView();

        return $this->render('administration/doctor/remove.html.twig', $arr);
    }

    /**
     * @Route("/{doctor}/invite", name="administration_doctor_invite")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function inviteAction(Doctor $doctor, TranslatorInterface $translator, InviteEmailService $emailService)
    {
        if (!$doctor->isEnabled() || null !== $doctor->getLastLoginDate()) {
            $this->displayError($translator->trans('invite.danger.email_not_sent', [], 'administration_doctor'));
        } else {
            $emailService->inviteDoctor($doctor);
            $this->displaySuccess($translator->trans('invite.success.email_sent', [], 'administration_doctor'));
        }

        return $this->redirectToRoute('administration_doctors');
    }

    /**
     * @Route("/invite_all", name="administration_doctor_invite_all")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function inviteAllAction(TranslatorInterface $translator, InviteEmailService $emailService)
    {
        $doctors = $this->getDoctrine()->getRepository(Doctor::class)->findBy(['deletedAt' => null, 'lastLoginDate' => null, 'isEnabled' => true]);
        foreach ($doctors as $doctor) {
            $emailService->inviteDoctor($doctor);
        }
        $this->displaySuccess($translator->trans('invite.success.email_sent', [], 'administration_doctor'));

        return $this->redirectToRoute('administration_doctors');
    }

    /**
     * @Route("/{doctor}/toggle_login_enabled", name="administration_doctor_toggle_login_enabled")
     *
     * @return Response
     */
    public function toggleLoginEnabled(Doctor $doctor)
    {
        $doctor->setIsEnabled(!$doctor->isEnabled());
        $this->fastSave($doctor);

        return $this->redirectToRoute('administration_doctors');
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return array_merge(parent::getIndexBreadcrumbs(), [
            new Breadcrumb(
                $this->generateUrl('administration_doctors'),
                $this->getTranslator()->trans('doctors.title', [], 'administration')
            ),
        ]);
    }
}
