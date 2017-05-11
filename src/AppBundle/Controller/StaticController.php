<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/12/2016
 * Time: 01:58
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Newsletter;
use AppBundle\Form\Newsletter\RegisterForPreviewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class StaticController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $arr = [];
        $arr["message"] = "";
        $newsLetter = new Newsletter();
        $newsletterForm = $this->createForm(RegisterForPreviewType::class);
        $newsletterForm->setData($newsLetter);

        $newsletterForm->handleRequest($request);

        if ($newsletterForm->isSubmitted()) {
            if ($newsletterForm->isValid()) {
                $this->getDoctrine()->getManager()->persist($newsLetter);
                $this->getDoctrine()->getManager()->flush();


                $message = \Swift_Message::newInstance()
                    ->setSubject("Nachricht auf nodika")
                    ->setFrom($this->getParameter("mai1ler_email"))
                    ->setTo($this->getParameter("contact_email"))
                    ->setBody("Sie haben eine Kontaktanfrage auf nodika erhalten: \n\n" . json_encode($newsLetter->getCommunicationLines(), JSON_PRETTY_PRINT));
                $this->get('mailer')->send($message);

                $arr["message"] = "Vielen Dank! Ich melde mich zurück.";
            }
        }

        $arr["newsletter_form"] = $newsletterForm->createView();
        //get today's menus
        return $this->render(
            'static/index.html.twig', $arr
        );
    }
}