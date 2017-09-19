<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;

use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\Person;
use AppBundle\Enum\SubmitButtonType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/person")
 * @Security("has_role('ROLE_USER')")
 */
class PersonController extends BaseFrontendController
{
    /**
     * @Route("/", name="person_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $arr["person"] = $this->getPerson();
        return $this->render("dashboard/index.html.twig", $arr);
    }

    /**
     * @Route("/edit", name="person_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        $person = $this->getPerson();
        $myForm = $this->handleCrudForm(
            $request,
            $person,
            SubmitButtonType::EDIT,
            function ($form, $entity) {
                return $this->redirectToRoute("person_view");
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr["member"] = $member;
        $arr["person"] = $person;
        $arr["edit_form"] = $myForm->createView();
        return $this->render(
            'administration/organisation/member/person/edit.html.twig', $arr
        );

        return $this->render("dashboard/index.html.twig", $arr);
    }
}