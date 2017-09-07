<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:19
 */

namespace AppBundle\Controller\Frontend;


use AppBundle\Controller\Base\AccessBaseController;
use AppBundle\Entity\FrontendUser;
use AppBundle\Form\Access\FrontendUser\FrontendUserRegisterType;
use AppBundle\Form\Access\FrontendUser\FrontendUserSetPasswordType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccessController extends AccessBaseController
{
    /**
     * @Route("/login", name="access_login")
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $user = $this->getUser();
        if ($user instanceof FrontendUser) {
            return $this->redirectToRoute("frontend_dashboard_index");
        }

        $form = $this->getLoginForm($request, new FrontendUser(), "access");
        if ($form instanceof RedirectResponse) {
            return $form;
        }
        $arr["login_form"] = $form->createView();

        return $this->render(
            'access/login.html.twig', $arr
        );
    }

    /**
     * @Route("/", name="access_default")
     * @param Request $request
     * @return Response
     */
    public function defaultAction(Request $request)
    {
        return $this->redirectToRoute("access_login");
    }

    /**
     * @Route("/register", name="access_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function registerAction(Request $request)
    {
        $user = FrontendUser::createNewFrontendUser();
        //random password at start because we want to confirm email first
        $user->setPlainPassword(uniqid());

        $form = $this->getRegisterForm(
            $request,
            $this->createForm(FrontendUserRegisterType::class),
            $user,
            $this->getDoctrine()->getRepository("AppBundle:FrontendUser"),
            function ($user) {
                /* @var $user FrontendUser */
                $user->setEmail($user->getContactEmail());
                if (!$user->isAcceptedAgb()) {
                    $this->displayError($this->get("translator")->trans("error.must_accept_agb", [], "access"));
                }
                return $user->isAcceptedAgb();
            },
            function ($user) {
                /* @var $user FrontendUser */
                return $this->generateUrl(
                    "access_register_confirm",
                    ["confirmationToken" => $user->getResetHash()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            },
            function ($user) {
                $translate = $this->get("translator");
                $receiverNotification = $this->getDoctrine()->getRepository("AppBundle:Setting")->getFrontendRegisterEmailReceiver();
                if ($receiverNotification != null) {
                    $message = \Swift_Message::newInstance()
                        ->setSubject($translate->trans("access_registered.subject", [], "notification_emails"))
                        ->setFrom($this->getParameter("mailer_email"))
                        ->setTo($receiverNotification)
                        ->setBody($translate->trans("access_registered.message", ["%site_name%" => $this->getParameter("site_name")], "notification_emails"));
                    $this->get('mailer')->send($message);
                }

                /* @var $user FrontendUser */
                return $this->redirectToRoute("access_register_thanks");
            }
        );
        if ($form instanceof RedirectResponse) {
            return $form;
        }

        $arr["register_form"] = $form->createView();
        return $this->render(
            'access/register.html.twig', $arr
        );
    }

    /**
     * @Route("/register/thanks", name="access_register_thanks")
     * @param Request $request
     * @return Response
     */
    public function registerThanksAction(Request $request)
    {
        return $this->render(
            'access/register_thanks.html.twig'
        );
    }

    /**
     * @Route("/reset", name="access_reset")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resetAction(Request $request)
    {
        $form = $this->getResetForm(
            $request,
            $this->getDoctrine()->getRepository("AppBundle:FrontendUser"),
            function ($user) {
                /* @var $user FrontendUser */
                return $this->generateUrl(
                    "access_reset_confirm",
                    ["confirmationToken" => $user->getResetHash()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            },
            function ($user) {
                return $this->redirectToRoute("access_reset_done");
            }
        );
        if ($form instanceof RedirectResponse) {
            return $form;
        }
        $arr["reset_form"] = $form->createView();
        return $this->render(
            'access/reset.html.twig', $arr
        );
    }

    /**
     * @Route("/reset/done", name="access_reset_done")
     * @param Request $request
     * @return Response
     */
    public function resetDoneAction(Request $request)
    {
        return $this->render(
            'access/reset_done.html.twig'
        );
    }

    /**
     * @Route("/reset/{confirmationToken}", name="access_reset_confirm")
     * @param Request $request
     * @param $confirmationToken
     * @return Response
     */
    public function resetConfirmAction(Request $request, $confirmationToken)
    {
        $result = $this->processConfirmationToken(
            $request,
            $this->getDoctrine()->getRepository("AppBundle:FrontendUser"),
            $confirmationToken,
            $this->createForm(FrontendUserSetPasswordType::class)
        );
        if ($result instanceof Response) {
            return $result;
        } else if ($result === true) {
            return $this->redirectToRoute("access_adverts");
        } else {
            $arr["reset_password_form"] = $result->createView();
            return $this->render(
                'access/reset_confirm.html.twig', $arr
            );
        }
    }

    /**
     * @Route("/register/{confirmationToken}", name="access_register_confirm")
     * @param Request $request
     * @param $confirmationToken
     * @return Response
     */
    public function registerConfirmAction(Request $request, $confirmationToken)
    {
        $result = $this->processConfirmationToken(
            $request,
            $this->getDoctrine()->getRepository("AppBundle:FrontendUser"),
            $confirmationToken,
            $this->createForm(FrontendUserSetPasswordType::class)
        );
        if ($result instanceof Response) {
            return $result;
        } else if ($result === true) {
            return $this->redirectToRoute("access_dashboard_index");
        } else {
            $arr["register_confirm_form"] = $result->createView();
            return $this->render(
                'access/register_confirm.html.twig', $arr
            );
        }
    }


    /**
     * @Route("/login_check", name="access_login_check")
     * @param Request $request
     */
    public function loginCheck(Request $request)
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="access_logout")
     * @param Request $request
     */
    public function logoutAction(Request $request)
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}