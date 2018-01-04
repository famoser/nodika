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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class StaticController extends BaseController
{
    /**
     * @Route("/", name="homepage")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        dump($this->getParameter("param1"));
        $arr = [];
        $arr['is_logged_in'] = $this->getUser() instanceof FrontendUser;

        $form = $this->handleFormDoctrinePersist(
            $this->createForm(RegisterForPreviewType::class),
            $request,
            new Newsletter(),
            function ($form, $newsletter) {
                /* @var FormInterface $form */
                /* @var Newsletter $newsletter */
                $this->get('app.email_service')->sendTextEmail(
                    $this->getParameter('CONTACT_EMAIL'),
                    'Kontaktanfrage von nodika',
                    "Sie haben eine Kontaktanfrage auf nodika erhalten: \n".
                    "\nListe: ".$newsletter->getChoice().
                    "\nEmail: ".$newsletter->getEmail().
                    "\nVorname: ".$newsletter->getGivenName().
                    "\nNachname: ".$newsletter->getFamilyName().
                    "\nNachricht: ".$newsletter->getMessage()
                );

                $translator = $this->get('translator');

                $this->displaySuccess($translator->trans('index.thanks_for_contact_form', [], 'static'));

                return $form;
            }
        );
        $arr['newsletter_form'] = $form->createView();

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
}
