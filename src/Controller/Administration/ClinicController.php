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
use App\Entity\Clinic;
use App\Form\Clinic\ClinicType;
use App\Form\Clinic\RemoveType;
use App\Helper\DoctrineHelper;
use App\Model\Breadcrumb;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/clinics')]
class ClinicController extends NewBaseController
{
    #[Route(path: '/new', name: 'administration_clinic_new')]
    public function new(Request $request, ManagerRegistry $registry, TranslatorInterface $translator): Response
    {
        $clinic = new Clinic();
        $form = $this->createForm(ClinicType::class, $clinic)
            ->add('submit', SubmitType::class, ['label' => 'new.submit', 'translation_domain' => 'administration_clinic']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            DoctrineHelper::persistAndFlush($registry, $clinic);

            $message = $translator->trans('new.success', [], 'administration_clinic');
            $this->addFlash('success', $message);

            return $this->redirectToRoute('administration_clinics');
        }

        return $this->render('administration/clinic/new.html.twig', ['form' => $form->createView(), 'breadcrumbs' => $this->getBreadcrumbs($translator)]);
    }

    #[Route(path: '/{clinic}/edit', name: 'administration_clinic_edit')]
    public function edit(Request $request, Clinic $clinic, ManagerRegistry $registry, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ClinicType::class, $clinic)
            ->add('submit', SubmitType::class, ['label' => 'edit.submit', 'translation_domain' => 'administration_clinic']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            DoctrineHelper::persistAndFlush($registry, $clinic);

            $message = $translator->trans('edit.success', [], 'administration_clinic');
            $this->addFlash('success', $message);

            return $this->redirect($this->generateUrl('administration_clinics'));
        }

        return $this->render('administration/clinic/edit.html.twig', ['form' => $form->createView(), 'breadcrumbs' => $this->getBreadcrumbs($translator)]);
    }

    /**
     * @Route("/{clinic}/remove", name="administration_clinic_remove").
     */
    public function remove(Request $request, Clinic $clinic, ManagerRegistry $registry, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(RemoveType::class, $clinic)
            ->add('remove', SubmitType::class, ['translation_domain' => 'common_form', 'label' => 'submit.delete']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $clinic->delete();
            DoctrineHelper::persistAndFlush($registry, $clinic);

            return $this->redirectToRoute('administration_clinics');
        }

        return $this->render('administration/clinic/remove.html.twig', ['form' => $form->createView(), 'breadcrumbs' => $this->getBreadcrumbs($translator)]);
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
                $this->generateUrl('administration_clinics'),
                $translator->trans('clinics.title', [], 'administration')
            ),
        ];
    }
}
