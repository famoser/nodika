<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:19
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\User;
use AppBundle\Form\Access\LoginType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }
        dump($error);
        $this->get('session')->getFlashBag()->set('error', "Login fehlgeschlagen");

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $user = new User();
        $user->setEmail($lastUsername);

        $loginForm = $this->get("form.factory")->createNamedBuilder(null)
            ->add("_username", EmailType::class)
            ->add("_password", PasswordType::class)
            ->add("submit", SubmitType::class)
            ->getForm();
        $loginForm->setData(["_username" => $user->getEmail()]);

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


        //get today's menus
        return $this->render(
            'access/login.html.twig', $arr
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