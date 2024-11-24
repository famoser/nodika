<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseFormController;
use App\Entity\Setting;
use App\Form\Doctor\EditAccountType;
use App\Form\Traits\User\ChangePasswordType;
use App\Helper\DoctrineHelper;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/account')]
class AccountController extends BaseFormController
{
    #[Route(path: '/', name: 'account_index')]
    public function index(Request $request, ManagerRegistry $registry, TranslatorInterface $translator): Response
    {
        $arr = [];

        $user = $this->getUser();
        $arr['user'] = $user;

        // change password form
        $form = $this->handleForm(
            $this->createForm(ChangePasswordType::class, $user)
                ->add('form.change_password', SubmitType::class, ['translation_domain' => 'account', 'label' => 'index.change_password']),
            $request,
            function ($form) use ($user, $registry, $translator) {
                if (
                    $user->getPlainPassword() !== $user->getRepeatPlainPassword()
                    || '' === $user->getPlainPassword()
                ) {
                    $this->displaySuccess($translator->trans('reset.danger.passwords_do_not_match', [], 'login'));

                    return $form;
                }

                $user->setPassword();
                DoctrineHelper::persistAndFlush($registry, ...[$user]);
                $this->displaySuccess($translator->trans('reset.success.password_set', [], 'login'));

                return $form;
            }
        );
        $arr['change_password_form'] = $form->createView();

        $setting = $registry->getRepository(Setting::class)->findSingle();
        if ($setting->getDoctorsCanEditSelf()) {
            // edit account form
            $form = $this->handleForm(
                $this->createForm(EditAccountType::class, $user)
                    ->add('form.save', SubmitType::class, ['translation_domain' => 'common_form', 'label' => 'submit.update']),
                $request,
                function ($form) use ($user, $registry, $translator) {
                    $this->displaySuccess($translator->trans('successful.update', [], 'common_form'));
                    DoctrineHelper::persistAndFlush($registry, ...[$user]);

                    return $form;
                }
            );
            $arr['update_form'] = $form->createView();
        }

        return $this->render('account/index.html.twig', $arr);
    }
}
