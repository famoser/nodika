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
                $this->get("app.email_service")->sendContactMessage($entity);

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