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
use App\Entity\Clinic;
use App\Form\Clinic\RemoveType;
use App\Model\Breadcrumb;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[\Symfony\Component\Routing\Attribute\Route(path: '/clinics')]
class ClinicController extends BaseController
{
    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/new', name: 'administration_clinic_new')]
    public function new(Request $request)
    {
        $myForm = $this->handleCreateForm($request, new Clinic());

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/clinic/new.html.twig', $arr);
    }

    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/{clinic}/edit', name: 'administration_clinic_edit')]
    public function edit(Request $request, Clinic $clinic)
    {
        $myForm = $this->handleUpdateForm($request, $clinic);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/clinic/edit.html.twig', $arr);
    }

    /**
     * deactivated because not safe
     * Route("/{clinic}/remove", name="administration_clinic_remove").
     *
     * @return Response
     */
    public function remove(Request $request, Clinic $clinic)
    {
        $canDelete = 0 === $clinic->getEvents()->count();
        $myForm = $this->handleForm(
            $this->createForm(RemoveType::class, $clinic)
                ->add('remove', SubmitType::class, ['translation_domain' => 'common_form', 'label' => 'submit.delete']),
            $request,
            function () use ($clinic, $canDelete): \Symfony\Component\HttpFoundation\RedirectResponse {
                $clinic->delete();
                if ($canDelete) {
                    $this->fastRemove($clinic);
                } else {
                    $clinic->delete();
                    $this->fastSave($clinic);
                }

                return $this->redirectToRoute('administration_clinics');
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['can_delete'] = $canDelete;
        $arr['form'] = $myForm->createView();

        return $this->render('administration/clinic/remove.html.twig', $arr);
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
                $this->generateUrl('administration_clinics'),
                $this->getTranslator()->trans('clinics.title', [], 'administration')
            ),
        ]);
    }
}
