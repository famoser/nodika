<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 11:35
 */

namespace App\Controller;

use App\Controller\Base\BaseLoginController;
use App\Entity\FrontendUser;
use App\Form\Traits\User\ChangePasswordType;
use App\Form\Traits\User\LoginType;
use App\Form\Traits\User\RecoverType;
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/login")
 */
class LoginController extends BaseLoginController
{
    /**
     * @Route("/", name="login_index")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $user = new FrontendUser();
        $form = $this->createForm(LoginType::class, $user);
        $form->add("form.login", SubmitType::class);
        $this->checkLoginForm($request, $user, $form);
        $arr["form"] = $form->createView();
        return $this->render('login/index.html.twig', $arr);
    }

    /**
     * @Route("/recover", name="login_recover")
     *
     * @param Request $request
     * @param EmailServiceInterface $emailService
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function recoverAction(Request $request, EmailServiceInterface $emailService, TranslatorInterface $translator)
    {
        $form = $this->handleForm(
            $this->createForm(RecoverType::class)
                ->add("form.recover", SubmitType::class),
            $request,
            function ($form) use ($emailService, $translator) {
                /* @var FormInterface $form */

                //display success
                $this->displaySuccess($translator->trans("recover.success.email_sent", [], "login"));

                //check if user exists
                $exitingUser = $this->getDoctrine()->getRepository(FrontendUser::class)->findOneBy(["email" => $form->getData()["email"]]);
                if (null === $exitingUser) {
                    return $form;
                }

                //create new reset hash
                $exitingUser->setResetHash();
                $this->fastSave($exitingUser);

                //sent according email
                $emailService->sendActionEmail(
                    $exitingUser->getEmail(),
                    $translator->trans("recover.email.reset_password.subject", [], "login"),
                    $translator->trans("recover.email.reset_password.message", [], "login"),
                    $translator->trans("recover.email.reset_password.action_text", [], "login"),
                    $this->generateUrl("login_reset", ["resetHash" => $exitingUser->getResetHash()], UrlGeneratorInterface::ABSOLUTE_URL)
                );

                return $form;
            }
        );
        $arr["form"] = $form->createView();
        return $this->render('login/recover.html.twig', $arr);
    }

    /**
     * @Route("/reset/{resetHash}", name="login_reset")
     *
     * @param Request $request
     * @param $resetHash
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function resetAction(Request $request, $resetHash, TranslatorInterface $translator)
    {
        $user = $this->getDoctrine()->getRepository(FrontendUser::class)->findOneBy(["resetHash" => $resetHash]);
        if (null === $user) {
            return $this->render('login/invalid.html.twig');
        }

        $form = $this->handleForm(
            $this->createForm(ChangePasswordType::class, $user, ["data_class" => FrontendUser::class])
                ->add("form.set_password", SubmitType::class),
            $request,
            function ($form) use ($user, $translator, $request) {
                //check for valid password
                if ($user->getPlainPassword() != $user->getRepeatPlainPassword()) {
                    $this->displayError($translator->trans("reset.error.passwords_do_not_match", [], "login"));
                    return $form;
                }

                //display success
                $this->displaySuccess($translator->trans("reset.success.password_set", [], "login"));

                //set new password & save
                $user->setPassword();
                $user->setResetHash();
                $this->fastSave($user);

                //login user & redirect
                $this->loginUser($request, $user);
                return $this->redirectToRoute("index_index");
            }
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr["form"] = $form->createView();
        return $this->render('login/reset.html.twig', $arr);
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="login_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}
