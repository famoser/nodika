<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 14:50
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Organisation;
use AppBundle\Form\Organisation\NewOrganisationType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisation")
 */
class OrganisationController extends BaseController
{
    /**
     * @Route("/new", name="organisation_new")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $newOrganisationForm = $this->createForm(NewOrganisationType::class);
        $arr = [];

        $organisation = Organisation::createFromPerson($this->getPerson());
        $newOrganisationForm->setData($organisation);
        $newOrganisationForm->handleRequest($request);

        if ($newOrganisationForm->isSubmitted()) {
            if ($newOrganisationForm->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();

                return $this->redirectToRoute("organisation_start", ["organisation" => $organisation->getId()]);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["new_organisation_form"] = $newOrganisationForm->createView();
        return $this->render(
            'organisation/new.html.twig', $arr
        );
    }

    /**
     * @Route("/start/{organisation}", name="organisation_start")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public
    function startAction(Request $request, Organisation $organisation)
    {
        return $this->render(
            'organisation/start.html.twig', []
        );
    }
}