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
use App\Form\Doctor\EditAccountType;
use App\Form\Traits\User\ChangePasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/account")
 * @Security("has_role('ROLE_USER')")
 */
class AccountController extends BaseFormController
{
    /**
     * @Route("/", name="account_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        $user = $this->getUser();
        $arr["user"] = $user;

        $form = $this->handleForm(
            $this->createForm(ChangePasswordType::class, $user)
                ->add("form.change_password", SubmitType::class),
            $request,
            function ($form) use ($user, $translator) {
                if (
                    $user->getPlainPassword() != $user->getRepeatPlainPassword() ||
                    $user->getPlainPassword() == ""
                ) {
                    $this->displayError($translator->trans("error.passwords_do_not_match", [], "access"));
                    return $form;
                }

                $user->setPassword();
                $this->fastSave($user);
                $this->displaySuccess($translator->trans("success.password_set", [], "access"));
                return $form;
            }
        );

        $arr["change_password_form"] = $form->createView();

        $form = $this->handleForm(
            $this->createForm(EditAccountType::class, $user)
                ->add("form.save", SubmitType::class),
            $request,
            function ($form) use ($user, $translator) {
                $this->displaySuccess($translator->trans("successful.update", [], "common_form"));
                $this->fastSave($user);
                return $form;
            }
        );

        $arr["update_form"] = $form->createView();


        return $this->render('account/index.html.twig', $arr);
    }
}
