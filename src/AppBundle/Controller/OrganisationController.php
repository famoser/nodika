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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisation")
 * @Security("has_role('ROLE_USER')")
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
                $organisation->setActiveEnd(new \DateTime("today + 31 days"));
                $organisation->setIsActive(true);
                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();

                return $this->redirectToRoute("organisation_setup", ["organisation" => $organisation->getId()]);
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
     * @Route("/setup/{organisation}", name="organisation_setup")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function setupAction(Request $request, Organisation $organisation)
    {
        $setupStatus = $this->getDoctrine()->getRepository("AppBundle:Organisation")->getSetupStatus($organisation);
        return $this->render(
            'organisation/setup.html.twig', ["organisation" => $organisation, "setupStatus" => $setupStatus]
        );
    }

    /**
     * @Route("/events/{organisation}", name="organisation_events")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function eventsAction(Request $request, Organisation $organisation)
    {
        $events = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEvents($organisation, new \DateTime(), ">");
        return $this->render(
            'organisation/events.html.twig',
            ["organisation" => $organisation, "events" => $events]
        );
    }
}