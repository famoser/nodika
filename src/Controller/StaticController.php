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
use App\Entity\Newsletter;
use App\Form\Newsletter\RegisterForPreviewType;
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
     * @param Request $request
     *
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     * @return Response
     */
    public function indexAction(Request $request, TranslatorInterface $translator, EmailService $emailService)
    {
        $arr = [];
        if ($this->getUser() instanceof FrontendUser) {
            return $this->redirectToRoute("dashboard_index");
        }

        return $this->renderNoBackUrl(
            'static/index.html.twig',
            $arr,
            'this is the homepage'
        );
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     * @param $arr
     */
    private function processNewsletterForm(Request $request, TranslatorInterface $translator, EmailService $emailService, &$arr)
    {
        $form = $this->handleFormDoctrinePersist(
            $this->createForm(RegisterForPreviewType::class),
            $request,
            $translator,
            new Newsletter(),
            function ($form, $newsletter) use ($translator, $emailService) {
                /* @var FormInterface $form */
                /* @var Newsletter $newsletter */
                $emailService->sendTextEmail(
                    $this->getParameter('CONTACT_EMAIL'),
                    'Kontaktanfrage von nodika',
                    "Sie haben eine Kontaktanfrage auf nodika erhalten: \n" .
                    "\nListe: " . $newsletter->getChoice() .
                    "\nEmail: " . $newsletter->getEmail() .
                    "\nVorname: " . $newsletter->getGivenName() .
                    "\nNachname: " . $newsletter->getFamilyName() .
                    "\nNachricht: " . $newsletter->getMessage()
                );

                $this->displaySuccess($translator->trans('index.thanks_for_contact_form', [], 'static'));

                return $form;
            }
        );
        $arr['newsletter_form'] = $form->createView();
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
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     * @return Response
     */
    public function aboutAction(Request $request, TranslatorInterface $translator, EmailService $emailService)
    {
        $arr = [];
        $this->processNewsletterForm($request, $translator, $emailService, $arr);
        return $this->render('static/about.html.twig', $arr);
    }

    /**
     * @Route("/contact", name="contact")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     * @return Response
     */
    public function contactAction(Request $request, TranslatorInterface $translator, EmailService $emailService)
    {
        $arr = [];
        $this->processNewsletterForm($request, $translator, $emailService, $arr);
        return $this->render('static/contact.html.twig', $arr);
    }
}
