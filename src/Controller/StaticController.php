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

use App\Controller\Base\BaseController;
use App\Entity\FrontendUser;
use App\Form\ContactRequest\ContactRequestType;
use App\Model\ContactRequest\ContactRequest;
use App\Service\EmailService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class StaticController extends BaseController
{
    /**
     * @Route("/", name="homepage")
     *
     * @return Response
     */
    public function indexAction()
    {
        $arr = [];
        if ($this->getUser() instanceof FrontendUser) {
            return $this->redirectToRoute('dashboard_index');
        }

        return $this->renderNoBackUrl(
            'static/index.html.twig',
            $arr,
            'this is the homepage'
        );
    }

    /**
     * @Route("/email/{identifier}", name="view_email")
     *
     * @param $identifier
     *
     * @return Response
     */
    public function emailAction($identifier)
    {
        $email = $this->getDoctrine()->getRepository('App:Email')->findOneBy(['identifier' => $identifier]);
        if (null === $email) {
            throw new NotFoundHttpException();
        }

        return $this->render('email/email.html.twig', ['email' => $email]);
    }

    /**
     * @Route("/about", name="about")
     *
     * @return Response
     */
    public function aboutAction()
    {
        $arr = [];
        return $this->render('static/about.html.twig', $arr);
    }

    /**
     * @Route("/contact", name="contact")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     *
     * @return Response
     */
    public function contactAction(Request $request, TranslatorInterface $translator, EmailService $emailService)
    {
        $arr = [];
        $this->processContactForm($request, $translator, $emailService, $arr);

        return $this->render('static/contact.html.twig', $arr);
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     * @param $arr
     */
    private function processContactForm(Request $request, TranslatorInterface $translator, EmailService $emailService, &$arr)
    {
        $form = $this->handleForm(
            $this->createForm(ContactRequestType::class),
            $request,
            $translator,
            new ContactRequest(),
            function ($form, $contactRequest) use ($translator, $emailService) {
                /* @var FormInterface $form */
                /* @var ContactRequest $contactRequest */
                $emailService->sendTextEmail(
                    $this->getParameter('CONTACT_EMAIL'),
                    'Kontaktanfrage von nodika',
                    "Sie haben eine Kontaktanfrage auf nodika erhalten: \n" .
                    "\nEmail: " . $contactRequest->getEmail() .
                    "\nName: " . $contactRequest->getName() .
                    "\nNachricht: " . $contactRequest->getMessage()
                );

                $this->displaySuccess($translator->trans('contact.thanks_for_contact_form', [], 'static'));

                return $this->createForm(ContactRequestType::class);
            }
        );
        $arr['contact_form'] = $form->createView();
    }
}
