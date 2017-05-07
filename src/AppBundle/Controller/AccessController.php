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

        $person = new Person();
        $registerForm->setData($person);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted()) {
            if ($registerForm->isValid()) {
                $this->getDoctrine()->getManager()->persist($person);
                $this->getDoctrine()->getManager()->flush();
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