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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[\Symfony\Component\Routing\Attribute\Route(path: '/settings')]
class SettingController extends BaseController
{
    #[\Symfony\Component\Routing\Attribute\Route(path: '/edit', name: 'administration_setting_edit')]
    public function edit(Request $request, FormFactoryInterface $factory, ManagerRegistry $registry): Response
    {
        $setting = $registry->getRepository(Setting::class)->findSingle();
        $settings = $this->handleUpdateForm(
            $request,
            $setting
        );

        $admins = $this->processSelectDoctors($registry, $request, $factory, 'admins',
            $registry->getRepository(Doctor::class)->findBy(['isAdministrator' => true]),
            function ($doctor, $value): void {
                /* @var Doctor $doctor */
                $doctor->setIsAdministrator($value);
            }
        );

        $emails = $this->processSelectDoctors($registry, $request, $factory, 'emails',
            $registry->getRepository(Doctor::class)->findBy(['isAdministrator' => true, 'receivesAdministratorMail' => true]),
            function ($doctor, $value): void {
                /* @var Doctor $doctor */
                $doctor->setReceivesAdministratorMail($value);
            }
        );

        return $this->render('administration/setting/edit.html.twig', ['settings' => $settings->createView(), 'admins' => $admins->createView(), 'emails' => $emails->createView()]);
    }

    /**
     * @param Doctor[] $data
     * @param callable $setProperty
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function processSelectDoctors(ManagerRegistry $registry, Request $request, FormFactoryInterface $factory, string $name, $data, \Closure $setProperty)
    {
        $adminForm = $factory->createNamedBuilder($name)
            ->setMapped(false)
            ->add('doctors', EntityType::class, ['multiple' => true, 'class' => Doctor::class, 'data' => $data, 'translation_domain' => 'entity_doctor', 'label' => 'entity.plural'])
            ->add('submit', SubmitType::class, ['translation_domain' => 'common_form', 'label' => 'submit.update'])
            ->getForm();
        $adminForm->handleRequest($request);

        if ($adminForm->isSubmitted() && $adminForm->isValid()) {
            $doctors = $registry->getRepository(Doctor::class)->findAll();
            $manager = $registry->getManager();
            foreach ($doctors as $doctor) {
                $setProperty($doctor, false);
                $manager->persist($doctor);
            }
            foreach ($adminForm->get('doctors')->getData() as $doctor) {
                $setProperty($doctor, true);
            }
            $manager->flush();
        }

        return $adminForm;
    }
}
