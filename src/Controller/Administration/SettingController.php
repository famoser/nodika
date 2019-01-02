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
use App\Entity\Setting;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/settings")
 */
class SettingController extends BaseController
{
    /**
     * @Route("/edit", name="administration_setting_edit")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request, FormFactoryInterface $factory)
    {
        $setting = $this->getDoctrine()->getRepository(Setting::class)->findSingle();
        $settings = $this->handleUpdateForm(
            $request,
            $setting
        );

        $admins = $this->processSelectDoctors($request, $factory, 'admins',
            $this->getDoctrine()->getRepository(Doctor::class)->findBy(['isAdministrator' => true]),
            function ($doctor, $value) {
                /* @var Doctor $doctor */
                $doctor->setIsAdministrator($value);
            }
        );

        $emails = $this->processSelectDoctors($request, $factory, 'emails',
            $this->getDoctrine()->getRepository(Doctor::class)->findBy(['isAdministrator' => true, 'receivesAdministratorMail' => true]),
            function ($doctor, $value) {
                /* @var Doctor $doctor */
                $doctor->setReceivesAdministratorMail($value);
            }
        );

        return $this->render('administration/setting/edit.html.twig', ['settings' => $settings->createView(), 'admins' => $admins->createView(), 'emails' => $emails->createView()]);
    }

    /**
     * @param Request $request
     * @param FormFactoryInterface $factory
     * @param Doctor[] $data
     * @param string $name
     * @param callable $setProperty
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function processSelectDoctors(Request $request, FormFactoryInterface $factory, $name, $data, $setProperty)
    {
        $adminForm = $factory->createNamedBuilder($name)
            ->setMapped(false)
            ->add('doctors', EntityType::class, ['multiple' => true, 'class' => Doctor::class, 'data' => $data, 'translation_domain' => 'entity_doctor', 'label' => 'entity.plural'])
            ->add('submit', SubmitType::class, ['translation_domain' => 'common_form', 'label' => 'submit.update'])
            ->getForm();
        $adminForm->handleRequest($request);

        if ($adminForm->isSubmitted() && $adminForm->isValid()) {
            $doctors = $this->getDoctrine()->getRepository(Doctor::class)->findAll();
            $manager = $this->getDoctrine()->getManager();
            foreach ($doctors as $doctor) {
                $setProperty($doctor, false);
                $manager->persist($doctor);
            }
            foreach ($adminForm->get("doctors")->getData() as $doctor) {
                $setProperty($doctor, true);
            }
            $manager->flush();
        }

        return $adminForm;
    }
}
