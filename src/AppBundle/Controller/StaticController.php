<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/12/2016
 * Time: 01:58
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Newsletter;
use AppBundle\Form\Newsletter\RegisterForPreviewType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class StaticController extends BaseController
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $arr = [];
        $arr["is_logged_in"] = $this->getUser() instanceof FrontendUser;

        $form = $this->handleFormDoctrinePersist(
            $this->createForm(RegisterForPreviewType::class),
            $request,
            new Newsletter(),
            function ($form, $entity) {
                /* @var FormInterface $form */
                /* @var Newsletter $entity */

                $message = \Swift_Message::newInstance()
                    ->setSubject("Nachricht auf nodika")
                    ->setFrom($this->getParameter("mailer_email"))
                    ->setTo($this->getParameter("contact_email"))
                    ->setBody("Sie haben eine Kontaktanfrage auf nodika erhalten: \n" .
                        "\nListe: " . $entity->getChoice() .
                        "\nEmail: " . $entity->getEmail() .
                        "\nVorname: " . $entity->getGivenName() .
                        "\nNachname: " . $entity->getFamilyName() .
                        "\nNachricht: " . $entity->getMessage());
                $this->get('mailer')->send($message);

                $translator = $this->get("translator");

                $this->displaySuccess($translator->trans("index.thanks_for_contact_form", [], "static"));
                return $form;
            }
        );
        $arr["newsletter_form"] = $form->createView();


        return $this->renderNoBackUrl(
            'static/index.html.twig', $arr, "this is the homepage"
        );
    }
}