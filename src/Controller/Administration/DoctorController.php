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

use App\Controller\Base\NewBaseController;
use App\Entity\Doctor;
use App\Form\Doctor\DoctorType;
use App\Form\Doctor\RemoveType;
use App\Helper\DoctrineHelper;
use App\Model\Breadcrumb;
use App\Service\InviteEmailService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/doctors')]
class DoctorController extends NewBaseController
{
    /**
     * checks if the email is already used, and shows an error to the user if so.
     */
    private function emailUnique(Doctor $doctor, TranslatorInterface $translator, ManagerRegistry $registry): bool
    {
        $existing = $registry->getRepository(Doctor::class)->findBy(['email' => $doctor->getEmail()]);
        if (\count($existing) > 0) {
            $this->displayError($translator->trans('new.danger.email_not_unique', [], 'administration_doctor'));

            return false;
        }

        return true;
    }

    #[Route(path: '/new', name: 'administration_doctor_new')]
    public function new(Request $request, ManagerRegistry $registry, TranslatorInterface $translator): Response
    {
        $doctor = new Doctor();
        $form = $this->createForm(DoctorType::class, $doctor)
            ->add('submit', SubmitType::class, ['label' => 'new.submit', 'translation_domain' => 'administration_doctor']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->emailUnique($doctor, $translator, $registry)) {
            DoctrineHelper::persistAndFlush($registry, $doctor);

            $message = $translator->trans('new.success', [], 'administration_doctor');
            $this->addFlash('success', $message);

            return $this->redirectToRoute('administration_doctors');
        }

        return $this->render('administration/doctor/new.html.twig', ['form' => $form->createView(), 'breadcrumbs' => $this->getBreadcrumbs($translator)]);
    }

    #[Route(path: '/{doctor}/edit', name: 'administration_doctor_edit')]
    public function edit(Request $request, Doctor $doctor, ManagerRegistry $registry, TranslatorInterface $translator): Response
    {
        $beforeEmail = $doctor->getEmail();
        $form = $this->createForm(DoctorType::class, $doctor)
            ->add('submit', SubmitType::class, ['label' => 'edit.submit', 'translation_domain' => 'administration_doctor']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && ($beforeEmail == $doctor->getEmail() || $this->emailUnique($doctor, $translator, $registry))) {
            DoctrineHelper::persistAndFlush($registry, $doctor);

            $message = $translator->trans('edit.success', [], 'administration_doctor');
            $this->addFlash('success', $message);

            return $this->redirect($this->generateUrl('administration_doctors'));
        }

        return $this->render('administration/doctor/edit.html.twig', ['form' => $form->createView(), 'breadcrumbs' => $this->getBreadcrumbs($translator)]);
    }

    #[Route(path: '/{doctor}/invite', name: 'administration_doctor_invite')]
    public function invite(Doctor $doctor, TranslatorInterface $translator, InviteEmailService $emailService): Response
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
     * @Route("/{doctor}/remove", name="administration_doctor_remove").
     */
    public function remove(Request $request, Doctor $doctor, ManagerRegistry $registry, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(RemoveType::class, $doctor)
            ->add('remove', SubmitType::class, ['translation_domain' => 'common_form', 'label' => 'submit.delete']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $doctor->delete();
            DoctrineHelper::persistAndFlush($registry, $doctor);

            return $this->redirectToRoute('administration_doctors');
        }

        return $this->render('administration/doctor/remove.html.twig', ['form' => $form->createView(), 'breadcrumbs' => $this->getBreadcrumbs($translator)]);
    }

    /**
     * @return Breadcrumb[]
     */
    private function getBreadcrumbs(TranslatorInterface $translator): array
    {
        return [
            new Breadcrumb(
                $this->generateUrl('administration_index'),
                $translator->trans('index.title', [], 'administration')
            ),
            new Breadcrumb(
                $this->generateUrl('administration_doctors'),
                $translator->trans('doctors.title', [], 'administration')
            ),
        ];
    }
}
