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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/doctors")
 */
class DoctorController extends BaseController
{
    /**
     * checks if the email is already used, and shows an error to the user if so.
     *
     * @param Doctor              $user
     * @param TranslatorInterface $translator
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
     * @param Request             $request
     * @param TranslatorInterface $translator
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
     * @param Request             $request
     * @param Doctor              $doctor
     * @param TranslatorInterface $translator
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
     * @param Request $request
     * @param Doctor  $doctor
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
     * @Route("/{doctor}/toggle_login_enabled", name="administration_doctor_toggle_login_enabled")
     *
     * @param Doctor $doctor
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
        return parent::getIndexBreadcrumbs() + [
            new Breadcrumb(
                $this->generateUrl('administration_doctors'),
                $this->getTranslator()->trans('doctors.title', [], 'administration')
            ),
        ];
    }
}
