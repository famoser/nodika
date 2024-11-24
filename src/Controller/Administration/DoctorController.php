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
use App\Helper\DoctrineHelper;
use App\Model\Breadcrumb;
use App\Service\InviteEmailService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[\Symfony\Component\Routing\Attribute\Route(path: '/doctors')]
class DoctorController extends BaseController
{
    /**
     * checks if the email is already used, and shows an error to the user if so.
     */
    private function emailNotUsed(Doctor $user, TranslatorInterface $translator, ManagerRegistry $registry): bool
    {
        $existing = $registry->getRepository(Doctor::class)->findBy(['email' => $user->getEmail()]);
        if (\count($existing) > 0) {
            $this->displayError($translator->trans('new.danger.email_not_unique', [], 'administration_doctor'));

            return false;
        }

        return true;
    }

    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/new', name: 'administration_doctor_new')]
    public function new(Request $request, TranslatorInterface $translator, ManagerRegistry $registry)
    {
        $user = new Doctor();
        $user->setPlainPassword(uniqid());
        $user->setPassword();
        $user->setRegistrationDate(new \DateTime());

        $myForm = $this->handleCreateForm(
            $request,
            $user,
            function () use ($user, $translator, $registry): bool {
                return $this->emailNotUsed($user, $translator, $registry);
            }
        );
        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/doctor/new.html.twig', $arr);
    }

    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/{doctor}/edit', name: 'administration_doctor_edit')]
    public function edit(Request $request, Doctor $doctor, TranslatorInterface $translator, ManagerRegistry $registry)
    {
        $beforeEmail = $doctor->getEmail();
        $myForm = $this->handleUpdateForm(
            $request,
            $doctor,
            function () use ($doctor, $translator, $beforeEmail, $registry): bool {
                if ($beforeEmail === $doctor->getEmail()) {
                    return true;
                }

                return $this->emailNotUsed($doctor, $translator, $registry);
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
    public function remove(Request $request, Doctor $doctor, ManagerRegistry $registry)
    {
        $canDelete = 0 === $doctor->getEvents()->count();
        $myForm = $this->handleForm(
            $this->createForm(RemoveType::class, $doctor),
            $request,
            function () use ($doctor, $canDelete, $registry): void {
                if ($canDelete) {
                    DoctrineHelper::removeAndFlush($registry, ...[$doctor]);
                } else {
                    $doctor->delete();
                    DoctrineHelper::persistAndFlush($registry, ...[$doctor]);
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
     * @return Response
     *
     * @throws \Exception
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/{doctor}/invite', name: 'administration_doctor_invite')]
    public function invite(Doctor $doctor, TranslatorInterface $translator, InviteEmailService $emailService): RedirectResponse
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
     * @return Response
     *
     * @throws \Exception
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/invite_all', name: 'administration_doctor_invite_all')]
    public function inviteAll(TranslatorInterface $translator, InviteEmailService $emailService, ManagerRegistry $registry): RedirectResponse
    {
        $doctors = $registry->getRepository(Doctor::class)->findBy(['deletedAt' => null, 'lastLoginDate' => null, 'isEnabled' => true]);
        foreach ($doctors as $doctor) {
            $emailService->inviteDoctor($doctor);
        }
        $this->displaySuccess($translator->trans('invite.success.email_sent', [], 'administration_doctor'));

        return $this->redirectToRoute('administration_doctors');
    }

    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/{doctor}/toggle_login_enabled', name: 'administration_doctor_toggle_login_enabled')]
    public function toggleLoginEnabled(Doctor $doctor, ManagerRegistry $registry): RedirectResponse
    {
        $doctor->setIsEnabled(!$doctor->isEnabled());
        DoctrineHelper::persistAndFlush($registry, ...[$doctor]);

        return $this->redirectToRoute('administration_doctors');
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs(): array
    {
        return array_merge(parent::getIndexBreadcrumbs(), [
            new Breadcrumb(
                $this->generateUrl('administration_doctors'),
                $this->getTranslator()->trans('doctors.title', [], 'administration')
            ),
        ]);
    }
}
