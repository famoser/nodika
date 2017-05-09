<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:19
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Person;
use AppBundle\Form\Access\LoginType;
use AppBundle\Form\Access\RegisterType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class AccessController extends BaseController
{
    /**
     * @Route("/login", name="access_login")
     */
    public function loginAction(Request $request)
    {
        $arr = [];

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }
        if ($error != null)
            $this->get('session')->getFlashBag()->set('error', "Login fehlgeschlagen");

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

        $user = new FrontendUser();
        $user->setEmail($lastUsername);


        $loginForm = $this->get("form.factory")->createNamedBuilder(
            null,
            FormType::class,
            ["_username" => $user->getEmail()],
            ["translation_domain" => "access"]
        )
            ->add("_username", EmailType::class)
            ->add("_password", PasswordType::class)
            ->add("login", SubmitType::class)
            ->getForm();


        $loginForm->handleRequest($request);

        if ($loginForm->isSubmitted()) {
            throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
            /*
            if ($newsletterForm->isValid()) {
                $myUser = $this->getDoctrine()->getRepository("AppBundle:User")->tryLogin($user);
                if ($myUser != null) {
                    if ($myUser->getIsActive()) {

                    }
                }
                $this->getDoctrine()->getManager()->persist($newsLetter);
                $this->getDoctrine()->getManager()->flush();
                $arr["message"] = "Vielen Dank! Ich melde mich zurück.";
            }
            */
        }

        $arr["login_form"] = $loginForm->createView();


        return $this->render(
            'access/login.html.twig', $arr
        );
    }

    /**
     * @Route("/reset", name="access_reset")
     */
    public function resetAction(Request $request)
    {

    }

    /**
     * @Route("/register", name="access_register")
     */
    public function registerAction(Request $request)
    {
        $registerForm = $this->createForm(RegisterType::class);
        $arr = [];

        $person = new Person();
        $registerForm->setData($person);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted()) {
            if ($registerForm->isValid()) {
                $existingUser = $this->getDoctrine()->getRepository("AppBundle:FrontendUser")->findOneBy(["email" => $person->getEmail()]);
                if ($existingUser == null) {
                    $arr["message"] = $this->get("translator")->trans("error.email_already_registered", [], "access");
                } else {

                    $this->getDoctrine()->getManager()->persist($person);
                    $this->getDoctrine()->getManager()->flush();

                    $user = FrontendUser::createFromPerson($person);
                    $translate = $this->get("translator");
                    $registerLink = $this->generateUrl(
                        "access_register_confirm",
                        ["confirmationToken" => $user->getResetHash()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $message = \Swift_Message::newInstance()
                        ->setSubject($translate->trans("register.subject", [], "access_emails"))
                        ->setFrom($this->getParameter("mailer_email"))
                        ->setTo($user->getEmail())
                        ->setBody($translate->trans(
                            "register.message",
                            ["register_link" => $registerLink],
                            "access_emails"));
                    $this->get('mailer')->send($message);
                    return $this->redirectToRoute("access_register_thanks");
                }
            } else {
                $arr["message"] = $this->get("translator")->trans("error.form_validation_failed", [], "common");
            }
        }

        $arr["register_form"] = $registerForm->createView();
        return $this->render(
            'access/register.html.twig', $arr
        );
    }

    /**
     * @Route("/register/thanks", name="access_register_thanks")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerThanksAction(Request $request)
    {
        return $this->render(
            'access/register_thanks.html.twig'
        );
    }

    /**
     * @Route("/register/{confirmationToken}", name="access_register_confirm")
     * @param Request $request
     * @param $confirmation
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerConfirmAction(Request $request, $confirmation)
    {
        $registerForm = $this->createForm(RegisterType::class);

        $person = new Person();
        $registerForm->setData($person);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted()) {
            if ($registerForm->isValid()) {


            }
        }

        $arr["register_form"] = $registerForm->createView();
        return $this->render(
            'access/register.html.twig', $arr
        );
    }

    /**
     * @Route("/login_check", name="access_login_check")
     */
    public function loginCheck(Request $request)
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }
}