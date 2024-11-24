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

use App\Entity\Doctor;
use App\Form\Traits\User\ChangePasswordType;
use App\Helper\DoctrineHelper;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/invite')]
class InviteController extends LoginController
{
    #[Route(path: '/doctor/{guid}', name: 'invite_doctor')]
    public function doctor(Request $request, ManagerRegistry $registry, $guid, TranslatorInterface $translator): RedirectResponse|Response
    {
        $user = $registry->getRepository(Doctor::class)->findOneBy(['invitationIdentifier' => $guid]);
        if (null === $user) {
            $this->displayError($translator->trans('invite_invalid.title', [], 'invite'));

            return $this->redirectToRoute('login');
        }

        // ensure user can indeed login
        if (!$user->isEnabled()) {
            $this->displayError($translator->trans('login.danger.login_disabled', [], 'login'));

            return $this->redirectToRoute('login');
        }

        // check if login still valid
        if (null !== $user->getLastLoginDate()) {
            $this->displayError($translator->trans('doctor.danger.already_login_occurred', [], 'invite'));
            $user->invitationAccepted();
            DoctrineHelper::persistAndFlush($registry, ...[$user]);

            return $this->redirectToRoute('login');
        }

        // consider displaying error & logout programmatically if already logged in

        // present set password form
        $form = $this->handleForm(
            $this->createForm(ChangePasswordType::class, $user, ['data_class' => Doctor::class])
                ->add('form.set_password', SubmitType::class, ['translation_domain' => 'login', 'label' => 'reset.set_password']),
            $request,
            function ($form) use ($user, $translator, $request, $registry) {
                // check for valid password
                if ($user->getPlainPassword() !== $user->getRepeatPlainPassword()) {
                    $this->displayError($translator->trans('reset.danger.passwords_do_not_match', [], 'login'));

                    return $form;
                }

                // display success
                $this->displaySuccess($translator->trans('doctor.success.access_created', [], 'invite'));

                // set new password & save
                $user->setPassword();
                $user->setResetHash();
                DoctrineHelper::persistAndFlush($registry, ...[$user]);

                // login user & redirect
                $this->loginUser($request, $user);

                return $this->redirectToRoute('index_index');
            }
        );

        if ($form instanceof Response) {
            return $form;
        }

        return $this->render('invite/doctor.html.twig', ['form' => $form->createView()]);
    }
}
